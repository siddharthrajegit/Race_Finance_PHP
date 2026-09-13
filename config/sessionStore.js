const session = require('express-session');
const db = require('./db');

const DEFAULT_TTL_MS = 1000 * 60 * 60 * 24 * 180; // 180 days (6 months)

class SqliteSessionStore extends session.Store {
  constructor(options = {}) {
    super();
    this.ttl = options.ttl || DEFAULT_TTL_MS;

    // Prepared statements for maximum performance and security
    this.getStmt = db.prepare('SELECT sess, expired FROM sessions WHERE sid = ?');
    this.setStmt = db.prepare(`
      INSERT INTO sessions (sid, sess, expired)
      VALUES (?, ?, ?)
      ON CONFLICT(sid) DO UPDATE SET sess = excluded.sess, expired = excluded.expired
    `);
    this.destroyStmt = db.prepare('DELETE FROM sessions WHERE sid = ?');
    this.touchStmt = db.prepare('UPDATE sessions SET expired = ? WHERE sid = ?');
    this.cleanupStmt = db.prepare('DELETE FROM sessions WHERE expired < ?');
    this.countStmt = db.prepare('SELECT COUNT(*) as count FROM sessions WHERE expired >= ?');
    this.clearStmt = db.prepare('DELETE FROM sessions');

    // Run cleanup immediately on boot
    this.cleanupExpired();

    // Schedule automatic pruning once every 24 hours (unref to avoid hanging process on shutdown)
    const cleanupInterval = setInterval(() => {
      this.cleanupExpired();
    }, 1000 * 60 * 60 * 24);
    if (cleanupInterval.unref) cleanupInterval.unref();
  }

  get(sid, cb) {
    try {
      const row = this.getStmt.get(sid);
      if (!row) {
        return cb(null, null);
      }

      if (row.expired && row.expired < Date.now()) {
        // Expired session - purge and return null
        this.destroyStmt.run(sid);
        return cb(null, null);
      }

      const sess = JSON.parse(row.sess);
      return cb(null, sess);
    } catch (err) {
      return cb(err);
    }
  }

  set(sid, sess, cb) {
    try {
      let expired;
      if (sess && sess.cookie && sess.cookie.expires) {
        expired = new Date(sess.cookie.expires).getTime();
      } else if (sess && sess.cookie && sess.cookie.maxAge) {
        expired = Date.now() + sess.cookie.maxAge;
      } else {
        expired = Date.now() + this.ttl;
      }

      const jsonStr = JSON.stringify(sess);
      this.setStmt.run(sid, jsonStr, expired);
      if (cb) cb(null);
    } catch (err) {
      if (cb) cb(err);
    }
  }

  destroy(sid, cb) {
    try {
      this.destroyStmt.run(sid);
      if (cb) cb(null);
    } catch (err) {
      if (cb) cb(err);
    }
  }

  touch(sid, sess, cb) {
    try {
      let expired;
      if (sess && sess.cookie && sess.cookie.expires) {
        expired = new Date(sess.cookie.expires).getTime();
      } else if (sess && sess.cookie && sess.cookie.maxAge) {
        expired = Date.now() + sess.cookie.maxAge;
      } else {
        expired = Date.now() + this.ttl;
      }

      this.touchStmt.run(expired, sid);
      if (cb) cb(null);
    } catch (err) {
      if (cb) cb(err);
    }
  }

  length(cb) {
    try {
      const row = this.countStmt.get(Date.now());
      cb(null, row.count);
    } catch (err) {
      cb(err);
    }
  }

  clear(cb) {
    try {
      this.clearStmt.run();
      if (cb) cb(null);
    } catch (err) {
      if (cb) cb(err);
    }
  }

  cleanupExpired() {
    try {
      this.cleanupStmt.run(Date.now());
    } catch (err) {
      console.error('Session cleanup error:', err.message);
    }
  }
}

module.exports = SqliteSessionStore;
