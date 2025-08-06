const { RateLimiterMemory } = require('rate-limiter-flexible');

class RateLimiter {
  constructor() {
    // Rate limiters for different actions
    this.connectionLimiter = new RateLimiterMemory({
      keyGenerator: (req) => req.ip || req.connection.remoteAddress,
      points: 10, // Number of connections
      duration: 60, // Per 60 seconds
    });
    
    this.eventLimiter = new RateLimiterMemory({
      keyGenerator: (req) => `${req.customer_id}:${req.user_id}`,
      points: 100, // Number of events
      duration: 60, // Per 60 seconds
    });
    
    this.messageLimiter = new RateLimiterMemory({
      keyGenerator: (req) => req.customer_id,
      points: 1000, // Number of messages
      duration: 3600, // Per hour
    });
  }
  
  async checkLimit(customerId, userId, type = 'event') {
    try {
      let limiter;
      let key;
      
      switch (type) {
        case 'connection':
          limiter = this.connectionLimiter;
          key = customerId;
          break;
        case 'event':
          limiter = this.eventLimiter;
          key = `${customerId}:${userId}`;
          break;
        case 'message':
          limiter = this.messageLimiter;
          key = customerId;
          break;
        default:
          return { allowed: true };
      }
      
      await limiter.consume(key);
      
      return { allowed: true };
      
    } catch (rejRes) {
      return {
        allowed: false,
        remainingPoints: rejRes.remainingPoints,
        msBeforeNext: rejRes.msBeforeNext
      };
    }
  }
  
  async checkConnectionLimit(ipAddress) {
    try {
      await this.connectionLimiter.consume(ipAddress);
      return { allowed: true };
    } catch (rejRes) {
      return {
        allowed: false,
        remainingPoints: rejRes.remainingPoints,
        msBeforeNext: rejRes.msBeforeNext
      };
    }
  }
  
  async checkEventLimit(customerId, userId) {
    try {
      const key = `${customerId}:${userId}`;
      await this.eventLimiter.consume(key);
      return { allowed: true };
    } catch (rejRes) {
      return {
        allowed: false,
        remainingPoints: rejRes.remainingPoints,
        msBeforeNext: rejRes.msBeforeNext
      };
    }
  }
  
  async checkMessageLimit(customerId) {
    try {
      await this.messageLimiter.consume(customerId);
      return { allowed: true };
    } catch (rejRes) {
      return {
        allowed: false,
        remainingPoints: rejRes.remainingPoints,
        msBeforeNext: rejRes.msBeforeNext
      };
    }
  }
  
  // Reset rate limits (useful for testing or admin actions)
  async resetLimits(customerId, userId = null) {
    if (userId) {
      const eventKey = `${customerId}:${userId}`;
      await this.eventLimiter.delete(eventKey);
    }
    
    await this.messageLimiter.delete(customerId);
  }
  
  // Get current rate limit status
  async getLimitStatus(customerId, userId = null) {
    const status = {
      connection: { remaining: 10, resetTime: null },
      event: { remaining: 100, resetTime: null },
      message: { remaining: 1000, resetTime: null }
    };
    
    try {
      if (userId) {
        const eventKey = `${customerId}:${userId}`;
        const eventRes = await this.eventLimiter.get(eventKey);
        if (eventRes) {
          status.event.remaining = eventRes.remainingPoints;
          status.event.resetTime = new Date(Date.now() + eventRes.msBeforeNext);
        }
      }
      
      const messageRes = await this.messageLimiter.get(customerId);
      if (messageRes) {
        status.message.remaining = messageRes.remainingPoints;
        status.message.resetTime = new Date(Date.now() + messageRes.msBeforeNext);
      }
      
    } catch (error) {
      console.error('Error getting rate limit status:', error);
    }
    
    return status;
  }
}

module.exports = RateLimiter; 