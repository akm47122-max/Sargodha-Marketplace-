import type { IncomingMessage, ServerResponse } from 'http';
import fs from 'fs';
import path from 'path';

// Server-side secret token storage path (never committed to git)
const TOKEN_FILE_PATH = path.resolve(process.cwd(), '.telegram_token');

/**
 * Safely retrieves server-side bot token.
 * Precedence: process.env.TELEGRAM_BOT_TOKEN -> .telegram_token file
 */
export function getServerTelegramToken(): string {
  if (process.env.TELEGRAM_BOT_TOKEN && process.env.TELEGRAM_BOT_TOKEN !== 'YOUR_TELEGRAM_BOT_TOKEN') {
    return process.env.TELEGRAM_BOT_TOKEN.trim();
  }
  try {
    if (fs.existsSync(TOKEN_FILE_PATH)) {
      const stored = fs.readFileSync(TOKEN_FILE_PATH, 'utf-8').trim();
      if (stored) return stored;
    }
  } catch (e) {}
  return '';
}

/**
 * Stores updated bot token securely on server side
 */
export function setServerTelegramToken(token: string): boolean {
  try {
    fs.writeFileSync(TOKEN_FILE_PATH, token.trim(), { mode: 0o600 });
    return true;
  } catch (e) {
    return false;
  }
}

/**
 * Calls Telegram Bot API
 */
async function callTelegramApi(endpoint: string, payload: Record<string, any>, tokenOverride?: string) {
  const token = tokenOverride || getServerTelegramToken();
  if (!token) {
    throw new Error('Telegram Bot Token is not configured on server (TELEGRAM_BOT_TOKEN).');
  }

  const url = `https://api.telegram.org/bot${token}/${endpoint}`;
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  const data = (await response.json()) as any;
  if (!data.ok) {
    throw new Error(data.description || 'Telegram API request failed.');
  }
  return data.result;
}

/**
 * Helper to parse JSON body from incoming HTTP request
 */
function parseBody(req: IncomingMessage): Promise<any> {
  return new Promise((resolve, reject) => {
    let body = '';
    req.on('data', (chunk) => (body += chunk));
    req.on('end', () => {
      try {
        resolve(body ? JSON.parse(body) : {});
      } catch (err) {
        reject(err);
      }
    });
    req.on('error', reject);
  });
}

/**
 * Middleware handling /api/telegram/* requests
 */
export async function handleTelegramRequest(req: IncomingMessage, res: ServerResponse): Promise<boolean> {
  const url = req.url || '';
  if (!url.startsWith('/api/telegram/')) {
    return false;
  }

  res.setHeader('Content-Type', 'application/json');

  try {
    // 1. GET /api/telegram/status
    if (url === '/api/telegram/status' && req.method === 'GET') {
      const token = getServerTelegramToken();
      const hasToken = Boolean(token && token.length > 5);
      let botUsername = '';

      if (hasToken) {
        try {
          const me = await callTelegramApi('getMe', {});
          botUsername = me.username || '';
        } catch (e) {}
      }

      res.end(
        JSON.stringify({
          hasTokenConfigured: hasToken,
          channelId: '-1003328935535',
          botUsername,
        })
      );
      return true;
    }

    // 2. POST /api/telegram/save-token
    if (url === '/api/telegram/save-token' && req.method === 'POST') {
      const body = await parseBody(req);
      const token = body.token?.trim();
      if (token) {
        setServerTelegramToken(token);
        // Verify token with getMe
        try {
          const me = await callTelegramApi('getMe', {}, token);
          res.end(
            JSON.stringify({
              success: true,
              message: `Bot token verified! Connected as @${me.username}`,
              botUsername: me.username,
            })
          );
          return true;
        } catch (err: any) {
          res.end(
            JSON.stringify({
              success: false,
              error: `Invalid Bot Token: ${err.message}`,
            })
          );
          return true;
        }
      }
      res.end(JSON.stringify({ success: false, error: 'Empty token provided.' }));
      return true;
    }

    // 3. POST /api/telegram/test-connection
    if (url === '/api/telegram/test-connection' && req.method === 'POST') {
      try {
        const me = await callTelegramApi('getMe', {});
        res.end(
          JSON.stringify({
            success: true,
            botUsername: me.username,
            firstName: me.first_name,
            message: `Bot @${me.username} is online and operational.`,
          })
        );
      } catch (err: any) {
        res.end(JSON.stringify({ success: false, error: err.message }));
      }
      return true;
    }

    // 4. POST /api/telegram/send-test
    if (url === '/api/telegram/send-test' && req.method === 'POST') {
      const body = await parseBody(req);
      const targetChannel = body.channelId || '-1003328935535';
      const timestamp = new Date().toLocaleString('en-US', { timeZone: 'Asia/Karachi' });

      const text = `📢 <b>SargodhaMart Telegram Automation Test</b>\n\n` +
        `✅ <b>Status:</b> Telegram Bot integration is successfully connected and operational!\n` +
        `📍 <b>Target Channel:</b> <code>${targetChannel}</code>\n` +
        `🕒 <b>Server Time:</b> ${timestamp}\n\n` +
        `🛍️ <i>Direct trading & verified local employment for Sargodha, Shaheenabad, and Sillanwali.</i>`;

      try {
        const result = await callTelegramApi('sendMessage', {
          chat_id: targetChannel,
          text: text,
          parse_mode: 'HTML',
          reply_markup: {
            inline_keyboard: [
              [
                { text: '🌐 Open SargodhaMart', url: body.websiteUrl || 'https://sargodhamart.com' },
                { text: '💬 WhatsApp Channel', url: 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g' },
              ],
            ],
          },
        });

        res.end(
          JSON.stringify({
            success: true,
            messageId: result.message_id,
            message: `Test message successfully delivered to channel ${targetChannel} (Message ID: ${result.message_id})`,
          })
        );
      } catch (err: any) {
        res.end(
          JSON.stringify({
            success: false,
            error: err.message || 'Failed to send message to Telegram channel. Ensure bot is added as Admin in the channel.',
          })
        );
      }
      return true;
    }

    // 5. POST /api/telegram/publish
    if (url === '/api/telegram/publish' && req.method === 'POST') {
      const body = await parseBody(req);
      const targetChannel = body.channelId || '-1003328935535';
      const { type, item, websiteUrl } = body;

      if (!item) {
        res.end(JSON.stringify({ success: false, error: 'Missing content payload.' }));
        return true;
      }

      let messageText = '';
      const inlineKeyboard: Array<Array<{ text: string; url: string }>> = [];

      if (type === 'product') {
        const price = Number(item.price).toLocaleString();
        const locationText = [item.area, item.tehsilName || item.city, item.districtName || 'Sargodha']
          .filter(Boolean)
          .join(', ');

        messageText =
          `🛍️ <b>NEW VERIFIED LISTING ON SARGODHAMART</b>\n\n` +
          `📦 <b>${escapeHtml(item.title)}</b>\n` +
          `💰 <b>Price:</b> Rs. ${price}\n` +
          `📍 <b>Location:</b> ${escapeHtml(locationText)}\n` +
          `🏷️ <b>Condition:</b> ${escapeHtml(item.condition || 'Used')}\n\n` +
          `📝 <i>${escapeHtml((item.description || '').slice(0, 220))}${(item.description || '').length > 220 ? '...' : ''}</i>\n\n` +
          `🛡️ <i>Direct verified seller from Sargodha District</i>`;

        const row1 = [{ text: '🔍 View Product on Website', url: `${websiteUrl || 'https://sargodhamart.com'}/?product=${item.id}` }];
        inlineKeyboard.push(row1);

        const contactRow: Array<{ text: string; url: string }> = [];
        if (item.sellerPhone) {
          contactRow.push({ text: `📞 Call (${item.sellerPhone})`, url: `tel:${item.sellerPhone}` });
        }
        if (item.sellerPhone || item.sellerWhatsappGroup) {
          const rawWa = (item.sellerPhone || '').replace(/\D/g, '');
          const waNum = rawWa.startsWith('0') ? `92${rawWa.slice(1)}` : rawWa;
          contactRow.push({ text: '💬 WhatsApp', url: `https://wa.me/${waNum}?text=${encodeURIComponent(`Salam! Saw your listing "${item.title}" on SargodhaMart.`)}` });
        }
        if (contactRow.length > 0) inlineKeyboard.push(contactRow);
      } else if (type === 'job') {
        const typeLabel = item.postType === 'need_worker' ? '🏢 Employer Hiring' : '👤 Worker Seeking Employment';
        const locationText = [item.area, item.tehsilName || item.city, item.districtName || 'Sargodha']
          .filter(Boolean)
          .join(', ');

        messageText =
          `💼 <b>NEW LOCAL JOB / ROZGAR OPPORTUNITY</b>\n\n` +
          `📌 <b>${escapeHtml(item.title)}</b>\n` +
          `📋 <b>Category:</b> ${escapeHtml(typeLabel)}\n` +
          `📍 <b>Location:</b> ${escapeHtml(locationText)}\n` +
          (item.salaryOrPayment ? `💵 <b>Compensation:</b> ${escapeHtml(item.salaryOrPayment)}\n` : '') +
          `🛠️ <b>Skills:</b> ${escapeHtml(item.skills || 'General')}\n\n` +
          `📝 <i>${escapeHtml((item.description || '').slice(0, 200))}${(item.description || '').length > 200 ? '...' : ''}</i>\n\n` +
          `🛡️ <i>SargodhaMart Local Employment Hub</i>`;

        const row1 = [{ text: '💼 View Job Details', url: `${websiteUrl || 'https://sargodhamart.com'}/?job=${item.id}` }];
        inlineKeyboard.push(row1);

        const contactRow: Array<{ text: string; url: string }> = [];
        if (item.phone) {
          contactRow.push({ text: `📞 Call (${item.phone})`, url: `tel:${item.phone}` });
        }
        if (item.whatsapp || item.phone) {
          const rawWa = (item.whatsapp || item.phone).replace(/\D/g, '');
          const waNum = rawWa.startsWith('0') ? `92${rawWa.slice(1)}` : rawWa;
          contactRow.push({ text: '💬 WhatsApp', url: `https://wa.me/${waNum}?text=${encodeURIComponent(`Salam! Saw your job post "${item.title}" on SargodhaMart.`)}` });
        }
        if (contactRow.length > 0) inlineKeyboard.push(contactRow);
      } else if (type === 'announcement') {
        messageText =
          `📢 <b>OFFICIAL SARGODHAMART ANNOUNCEMENT</b>\n\n` +
          `⭐ <b>${escapeHtml(item.title)}</b>\n\n` +
          `${escapeHtml(item.message)}\n\n` +
          `🌐 <i>SargodhaMart - Buy • Sell • Jobs • Grow</i>`;

        if (item.buttonText && item.buttonUrl) {
          inlineKeyboard.push([{ text: item.buttonText, url: item.buttonUrl }]);
        }
      }

      try {
        const payload: Record<string, any> = {
          chat_id: targetChannel,
          text: messageText,
          parse_mode: 'HTML',
        };
        if (inlineKeyboard.length > 0) {
          payload.reply_markup = { inline_keyboard: inlineKeyboard };
        }

        const result = await callTelegramApi('sendMessage', payload);
        res.end(
          JSON.stringify({
            success: true,
            messageId: result.message_id,
            message: `Published to Telegram channel ${targetChannel} (Message ID: ${result.message_id})`,
          })
        );
      } catch (err: any) {
        res.end(
          JSON.stringify({
            success: false,
            error: err.message || 'Telegram broadcast failed.',
          })
        );
      }
      return true;
    }

    res.statusCode = 404;
    res.end(JSON.stringify({ error: 'Endpoint not found' }));
    return true;
  } catch (err: any) {
    res.statusCode = 500;
    res.end(JSON.stringify({ error: err.message || 'Internal Server Error' }));
    return true;
  }
}

function escapeHtml(text: string): string {
  return (text || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}
