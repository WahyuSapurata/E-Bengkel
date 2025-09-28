const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');

// Simpan user yang sudah disapa
let greetedUsers = new Set();

// Simpan outlet yang dipilih per user
let userOutletMap = new Map();

const client = new Client({
    authStrategy: new LocalAuth({
        clientId: "MotoCore-bot"
    }),
    puppeteer: {
        headless: true,
        executablePath: '/usr/bin/chromium-browser',  // ganti ke versi apt
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--single-process',
            '--disable-gpu'
        ]
    }
});

client.on('qr', qr => {
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('✅ WhatsApp Bot siap tanpa scan ulang!');
});

client.on('message', async msg => {
    const text = msg.body.toLowerCase();
    const user = msg.from;
    let reply = "";

    try {
        // ✅ Greeting khusus "halo"
        if (text.startsWith("halo")) {
            reply =
                "👋 Halo, selamat datang di *Boot MotoCore SYSTEM*!\n\n" +
                "Ketik *mulai* untuk melihat layanan yang tersedia.";
        }

        // ✅ Outlet
        else if (text === "mulai") {
            const response = await axios.get('https://adsmotor.id/api/bot/outlet');

            if (response.data && response.data.length > 0) {
                const outlets = response.data;
                reply = "🏢 *Daftar Outlet:*\n";
                outlets.forEach((outlet, index) => {
                    reply += `${index + 1}. ${outlet.nama_outlet}\n`; // Bisa ditambah alamat juga
                });
                reply += "\n👉 Balas dengan nomor outlet untuk memilih.";
                await msg.reply(reply);

                // Simpan list outlet sementara untuk user
                userOutletMap.set(user, { outlets, selected: null });
                return; // Tunggu user pilih outlet
            } else {
                reply = "❌ Outlet tidak ditemukan.";
            }
        }

        // ✅ Pilih outlet
        else if (userOutletMap.has(user) && !userOutletMap.get(user).selected) {
            const userData = userOutletMap.get(user);
            const choice = parseInt(text);

            if (!isNaN(choice) && choice > 0 && choice <= userData.outlets.length) {
                const selectedOutlet = userData.outlets[choice - 1];
                userData.selected = selectedOutlet; // simpan outlet terpilih
                userOutletMap.set(user, userData);

                reply = `✅ Outlet dipilih: *${selectedOutlet.nama_outlet}*\n\n` +
                    "📌 Pilihan Layanan:\n" +
                    "1️⃣ Cek Produk & Stok\n" +
                    "2️⃣ Info Harga Servis\n" +
                    "3️⃣ Jam & Alamat Bengkel\n" +
                    "4️⃣ Promo Bengkel\n\n" +
                    "👉 Balas dengan angka (1-4).";

            } else {
                reply = "❌ Pilihan tidak valid. Silakan pilih nomor outlet yang tersedia.";
            }
        }

        // ✅ Pilihan angka menu
        else if (text === "1") {
            reply = "🔎 Silakan ketik nama produk dengan awalan *ada, stok, harga*, contoh: *ada oli ...*, *stok ban ...* atau *harga kampas ...*.";
        } else if (text === "2") {
            reply = "💰 Harga servis:\n- Ganti oli: Rp 50.000\n- Servis ringan: Rp 150.000\n- Servis besar: Rp 300.000";
        } else if (text === "3") {
            reply = "⏰ Jam operasional:\nSenin–Sabtu 08:00–17:00\nMinggu libur.\n\n📍 Alamat:\nJl. Sudirman No.123, Jakarta\nGoogle Maps: https://goo.gl/maps/xxxx";
        } else if (text === "4") {
            reply = "🔥 Promo bulan ini: *Ganti oli → Gratis cek rem*.";
        }

        // ✅ Cari produk (via Laravel API) dengan uuid outlet
        // ✅ Cari produk (via Laravel API) dengan uuid_user
        else if ((text.includes("harga") || text.includes("stok") || text.includes("ada")) && userOutletMap.has(user)) {
            const userData = userOutletMap.get(user);
            if (!userData.selected) {
                reply = "❌ Silakan pilih outlet terlebih dahulu dengan ketik *mulai*.";
            } else {
                const stopWords = ["ada", "stok", "harga", "apa", "tersedia"];
                let queryWords = text.split(' ').filter(w => !stopWords.includes(w));
                const query = queryWords.join(' ');

                // Kirim request ke backend dengan uuid_user
                const response = await axios.get(`https://adsmotor.id/api/bot/produk`, {
                    params: {
                        q: query,
                        uuid_user: userData.selected.uuid_user // <-- ini dari outlet yang dipilih
                    }
                });

                if (response.data && response.data.length > 0) {
                    const formatRupiah = (number) => {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(number);
                    };

                    // Loop semua produk dan gabungkan ke reply
                    const replyProduk = response.data.map(p => {
                        return `📦 *${p.nama}* (${p.merek})\n💰 Harga: ${formatRupiah(p.harga)}\n📊 Stok: ${p.stok}`;
                    }).join('\n\n');

                    // kirim pesan pertama (daftar produk)
                    await msg.reply(replyProduk);

                    // setelah 5 detik kirim menu lagi
                    setTimeout(() => {
                        msg.reply(
                            `✅ Outlet dipilih: *${userData.selected.nama_outlet}*\n\n` +
                            "📌 Pilihan Layanan:\n" +
                            "1️⃣ Cek Produk & Stok\n" +
                            "2️⃣ Info Harga Servis\n" +
                            "3️⃣ Jam & Alamat Bengkel\n" +
                            "4️⃣ Promo Bengkel\n\n" +
                            "👉 Balas dengan angka (1-4)."
                        );
                    }, 5000);

                } else {
                    await msg.reply("❌ Produk tidak ditemukan di outlet ini.");
                }
            }
        }

        // ✅ Default
        else {
            reply = "❓ Maaf, saya belum mengerti.\nKetik *mulai* untuk bantuan.";
        }

        if (reply) await msg.reply(reply);

    } catch (error) {
        const msgError = error.response ? JSON.stringify(error.response.data) : error.message;
        await msg.reply("⚠️ Terjadi kesalahan:\n" + msgError);
    }

});

client.initialize();
