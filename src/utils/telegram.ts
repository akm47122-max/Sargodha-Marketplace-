/**
 * SARGODHAMART - Telegram Automation Client Service
 * Communicates with server-side proxy /api/telegram/*
 * CRITICAL SECURITY: Bot Token is NEVER exposed to client, logs, or network requests.
 * Stored securely server-side as TELEGRAM_BOT_TOKEN or .telegram_token
 */

export interface TelegramStatusResponse {
  hasTokenConfigured: boolean;
  channelId: string;
  botUsername?: string;
  isEnabled?: boolean;
}

export interface TelegramActionResponse {
  success: boolean;
  message?: string;
  error?: string;
  messageId?: number | string;
  botUsername?: string;
  firstName?: string;
}

/**
 * Checks server-side Telegram Bot configuration status
 */
export async function checkTelegramStatus(): Promise<TelegramStatusResponse> {
  try {
    const res = await fetch('/api/telegram/status');
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
  } catch (err: any) {
    return {
      hasTokenConfigured: false,
      channelId: '-1003328935535',
      botUsername: '',
    };
  }
}

/**
 * Saves/updates Bot Token on server side securely
 * Value is transmitted over POST directly to server-side storage and never echoed back
 */
export async function saveTelegramToken(token: string): Promise<TelegramActionResponse> {
  try {
    const res = await fetch('/api/telegram/save-token', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token: token.trim() }),
    });
    return await res.json();
  } catch (err: any) {
    return {
      success: false,
      error: err.message || 'Failed to update bot token on server.',
    };
  }
}

/**
 * Tests connection to Telegram Bot API via getMe
 */
export async function testTelegramConnection(): Promise<TelegramActionResponse> {
  try {
    const res = await fetch('/api/telegram/test-connection', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    });
    return await res.json();
  } catch (err: any) {
    return {
      success: false,
      error: err.message || 'Failed to connect to Telegram Bot API.',
    };
  }
}

/**
 * Sends a test verification message to the specified Telegram Channel
 * Target Channel default: -1003328935535
 */
export async function sendTelegramTestMessage(
  channelId: string = '-1003328935535',
  websiteUrl?: string
): Promise<TelegramActionResponse> {
  try {
    const res = await fetch('/api/telegram/send-test', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        channelId,
        websiteUrl: websiteUrl || window.location.origin,
      }),
    });
    return await res.json();
  } catch (err: any) {
    return {
      success: false,
      error: err.message || 'Failed to send test broadcast to Telegram.',
    };
  }
}

/**
 * Publishes an approved product, job, or announcement to the Telegram Channel
 * Includes duplicate prevention check & contact buttons
 */
export async function broadcastToTelegram(
  type: 'product' | 'job' | 'announcement',
  item: any,
  channelId: string = '-1003328935535',
  websiteUrl?: string,
  force: boolean = false
): Promise<TelegramActionResponse> {
  // Safety checks
  if (!item) {
    return { success: false, error: 'Cannot broadcast empty content item.' };
  }

  // Never broadcast unapproved or rejected content
  if (type === 'product' && item.status !== 'published') {
    return { success: false, error: 'Only approved & published products can be broadcasted.' };
  }
  if (type === 'job' && item.status !== 'published') {
    return { success: false, error: 'Only approved & active jobs can be broadcasted.' };
  }
  if (type === 'announcement' && item.isActive === false) {
    return { success: false, error: 'Only active announcements can be broadcasted.' };
  }

  try {
    const res = await fetch('/api/telegram/publish', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        type,
        item,
        channelId,
        websiteUrl: websiteUrl || window.location.origin,
        force,
      }),
    });

    const data = await res.json();
    return data;
  } catch (err: any) {
    return {
      success: false,
      error: err.message ? err.message.replace(/bot[a-zA-Z0-9_-]+/g, 'bot[REDACTED]') : 'Network error broadcasting to Telegram.',
    };
  }
}
