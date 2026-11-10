/**
 * Token bucket for the whole process: a first line of defence in front of Laravel, whose
 * per-account limit (service_accounts.rate_limit_per_minute) is authoritative.
 */
export class TokenBucket {
  private tokens: number;
  private updatedAt: number;

  constructor(
    private readonly capacity: number,
    private readonly refillPerMinute: number,
    private readonly now: () => number = Date.now,
  ) {
    this.tokens = capacity;
    this.updatedAt = now();
  }

  tryTake(): boolean {
    const current = this.now();
    this.tokens = Math.min(this.capacity, this.tokens + ((current - this.updatedAt) / 60000) * this.refillPerMinute);
    this.updatedAt = current;

    if (this.tokens < 1) {
      return false;
    }

    this.tokens -= 1;

    return true;
  }
}
