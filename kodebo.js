process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
const TelegramBot = require('node-telegram-bot-api');
const { processApiRequest } = require('./lib/orderkuota-logic');

const TELEGRAM_TOKEN = '#';

const bot = new TelegramBot(TELEGRAM_TOKEN, { polling: true });

const userStates = {};

async function deleteLastBotMessage(chatId) {
  const state = userStates[chatId];
  if (state && state.lastBotMessageId) {
    try {
      await bot.deleteMessage(chatId, state.lastBotMessageId);
    } catch (e) {
    }
    state.lastBotMessageId = null;
  }
}

bot.onText(/\/start/, async (msg) => {
  await deleteLastBotMessage(msg.chat.id);
  const sent = await bot.sendMessage(
    msg.chat.id,
    'Selamat datang di OrderKuota Bot!\nKetik /otp untuk login OrderKuota.\n\n*Privasi Anda aman!* Semua proses dan data yang Anda kirim _tidak disimpan di database manapun_. Hanya Anda dan bot yang tahu data yang dikirimkan.',
    { parse_mode: 'Markdown' }
  );
  userStates[msg.chat.id] = { lastBotMessageId: sent.message_id };
});

bot.onText(/\/otp/, async (msg) => {
  await deleteLastBotMessage(msg.chat.id);
  userStates[msg.chat.id] = { step: 'awaiting_username' };
  const sent = await bot.sendMessage(msg.chat.id, 'Masukkan username OrderKuota Anda untuk request OTP:');
  userStates[msg.chat.id].lastBotMessageId = sent.message_id;
});

bot.onText(/\/verify/, async (msg) => {
  await deleteLastBotMessage(msg.chat.id);
  userStates[msg.chat.id] = { step: 'verify_awaiting_username' };
  const sent = await bot.sendMessage(msg.chat.id, 'Masukkan username OrderKuota Anda untuk verifikasi OTP:');
  userStates[msg.chat.id].lastBotMessageId = sent.message_id;
});

bot.on('message', async (msg) => {
  const chatId = msg.chat.id;
  const state = userStates[chatId];
  if (!state) return;

  // OTP flow
  if (state.step === 'awaiting_username' && !msg.text.startsWith('/')) {
    state.username = msg.text.trim();
    state.step = 'awaiting_password';
    await deleteLastBotMessage(chatId);
    const sent = await bot.sendMessage(chatId, 'Masukkan password OrderKuota Anda:');
    state.lastBotMessageId = sent.message_id;
    return;
  }
  if (state.step === 'awaiting_password' && !msg.text.startsWith('/')) {
    state.password = msg.text.trim();
    state.step = 'processing_otp';
    await deleteLastBotMessage(chatId);
    const sentReq = await bot.sendMessage(chatId, 'Meminta OTP...');
    state.lastBotMessageId = sentReq.message_id;
    try {
      const result = await processApiRequest({
        action: 'request_otp',
        username: state.username,
        password: state.password
      });
      if (result && result.success === false) {
        await deleteLastBotMessage(chatId);
        const sent = await bot.sendMessage(chatId, `❌ *Gagal request OTP!*
_${result.message || 'Terjadi kesalahan.'}_`, { parse_mode: 'Markdown' });
        state.lastBotMessageId = sent.message_id;
        delete userStates[chatId];
        return;
      }
      await deleteLastBotMessage(chatId);
      const sent = await bot.sendMessage(chatId, `✅ *OTP berhasil dikirim!*
Silakan cek email/nomor Anda dan masukkan kode OTP di bawah ini.\n\n_Respon server:_ \`${JSON.stringify(result)}\``, { parse_mode: 'Markdown' });
      state.lastBotMessageId = sent.message_id;
      state.step = 'verify_awaiting_otp';
      const sentOtp = await bot.sendMessage(chatId, 'Masukkan kode OTP yang Anda terima:');
      state.lastBotMessageId = sentOtp.message_id;
    } catch (err) {
      await deleteLastBotMessage(chatId);
      const sent = await bot.sendMessage(chatId, `❌ *Gagal request OTP!*
_${err.message}_`, { parse_mode: 'Markdown' });
      state.lastBotMessageId = sent.message_id;
      delete userStates[chatId];
    }
    return;
  }

  if (state.step === 'verify_awaiting_otp' && !msg.text.startsWith('/')) {
    state.otp = msg.text.trim();
    state.step = 'processing_verify';
    await deleteLastBotMessage(chatId);
    const sentVer = await bot.sendMessage(chatId, '🔄 Memverifikasi OTP...');
    state.lastBotMessageId = sentVer.message_id;
    try {
      const result = await processApiRequest({
        action: 'verify_otp',
        username: state.username,
        otp: state.otp
      });
      if (result && result.success === false) {
        await deleteLastBotMessage(chatId);
        const sent = await bot.sendMessage(chatId, `❌ *Gagal verifikasi OTP!*
_${result.message || 'Terjadi kesalahan.'}_`, { parse_mode: 'Markdown' });
        state.lastBotMessageId = sent.message_id;
        delete userStates[chatId];
        return;
      }
      await deleteLastBotMessage(chatId);
      const token = result?.results?.token || '-';
      const sent = await bot.sendMessage(chatId, `🎉 *Verifikasi Berhasil!*
Berikut token Anda (jaga kerahasiaannya):
\`${token}\`
\n_Respon server:_ \`${JSON.stringify(result)}\``, { parse_mode: 'Markdown' });
      state.lastBotMessageId = sent.message_id;
    } catch (err) {
      await deleteLastBotMessage(chatId);
      const sent = await bot.sendMessage(chatId, `❌ *Gagal verifikasi OTP!*
_${err.message}_`, { parse_mode: 'Markdown' });
      state.lastBotMessageId = sent.message_id;
    }
    delete userStates[chatId];
    return;
  }
});
