<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Coming Soon - BengkelApp Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Background image full cover with overlay and gradient */
        .hero-bg {
            position: relative;
            /* Gabungkan gradient dan gambar background */
            background-image:
                linear-gradient(135deg, rgba(44, 62, 80, 0.7) 0%, rgba(75, 110, 175, 0.7) 100%),
                url('https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/9f6c37a1-dd31-4f96-8aec-0feb8ac5477f.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 0 1rem;
            z-index: 0;
        }

        /* Animasi pulse lembut */
        .animate-pulse {
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.75;
            }
        }

        /* Overlay gelap semi transparan (optional, bisa dihapus jika sudah ada gradient di background-image) */
        .hero-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 0;
        }

        /* Konten di depan overlay dan background */
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 900px;
        }

        /* Countdown cards */
        .countdown-card {
            background: rgba(255 255 255 / 0.15);
            backdrop-filter: blur(10px);
            border-radius: 0.75rem;
            padding: 1.5rem 2rem;
            min-width: 90px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            color: #fff;
            font-weight: 700;
            font-size: 2rem;
            user-select: none;
        }

        .countdown-label {
            font-weight: 500;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #ddd;
        }

        /* Input and button styling */
        .input-email {
            padding: 0.75rem 1rem;
            border-radius: 9999px;
            border: none;
            outline: none;
            font-size: 1rem;
            width: 100%;
            max-width: 320px;
            margin-right: 1rem;
            color: #111;
        }

        .btn-submit {
            background-color: #2563eb;
            /* blue-600 */
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #1d4ed8;
            /* blue-700 */
        }

        @media (max-width: 640px) {
            .input-email {
                margin-right: 0;
                margin-bottom: 1rem;
                max-width: 100%;
            }

            .form-row {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>

<body class="bg-gray-900">

    <section class="hero-bg animate-pulse">
        <div class="hero-content">
            <h1 class="text-3xl sm:text-5xl font-extrabold mb-6 drop-shadow-lg">
                MotoCore SYSTEM Akan Segera Hadir!
            </h1>
            <p class="text-sm sm:text-xl mb-8 drop-shadow-md mx-auto">
                "Solusi Manajemen Bengkel Modern" <br>
                Aplikasi ini dirancang khusus untuk mempermudah pengelolaan bengkel Anda. Dengan fitur lengkap dan mudah
                digunakan, Anda dapat mengatur seluruh aktivitas operasional bengkel dalam satu sistem terintegrasi,
                mulai dari manajemen produk dan stok, pencatatan transaksi kasir, hingga pengelolaan keuangan dan
                laporan accounting yang akurat. <br>
                Peluncuran
                resmi
                pada <strong id="peluncuran"></strong>.
            </p>

            <div class="bg-white bg-opacity-50 p-2 pt-4 rounded-lg shadow-lg mb-8 flex justify-center">
                <img src="{{ asset('logo.png') }}" class="w-[350px]" alt="">
            </div>

            <div class="flex justify-center gap-6 flex-wrap">
                <div class="countdown-card">
                    <div id="days">0</div>
                    <div class="countdown-label">Hari</div>
                </div>
                <div class="countdown-card">
                    <div id="hours">0</div>
                    <div class="countdown-label">Jam</div>
                </div>
                <div class="countdown-card">
                    <div id="minutes">0</div>
                    <div class="countdown-label">Menit</div>
                </div>
                <div class="countdown-card">
                    <div id="seconds">0</div>
                    <div class="countdown-label">Detik</div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Countdown Timer (Target: January 1, 2025)
        function updateCountdown() {
            const targetDate = new Date('2025-09-18T00:00:00'); // biarkan sebagai Date object
            const now = new Date().getTime();
            const distance = targetDate.getTime() - now;

            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById('days').textContent = days;
                document.getElementById('hours').textContent = hours;
                document.getElementById('minutes').textContent = minutes;
                document.getElementById('seconds').textContent = seconds;

                // Format bulan & tahun peluncuran (contoh: "Oktober 2025")
                const options = {
                    month: 'long',
                    year: 'numeric'
                };
                document.getElementById('peluncuran').textContent = targetDate.toLocaleDateString('id-ID', options);
            } else {
                document.querySelector('.hero-content').innerHTML =
                    '<h2 class="text-4xl font-bold drop-shadow-lg">Peluncuran Sekarang!</h2>';
            }
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Form submission handler
        document.getElementById('subscribe-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = document.getElementById('email');
            const email = emailInput.value.trim();
            if (email && email.includes('@') && email.includes('.')) {
                alert('Terima kasih! Kami akan menghubungi Anda saat peluncuran.');
                emailInput.value = '';
            } else {
                alert('Silakan masukkan email yang valid.');
            }
        });
    </script>
</body>

</html>


{{-- <!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Coming Soon - BengkelApp Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
        /* Background gradasi gelap lembut */
        .hero-bg {
            background: linear-gradient(135deg, #2c3e50 0%, #4b6eaf 100%);
        }

        /* Animasi pulse lembut */
        .animate-pulse {
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.75;
            }
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-200">
    <!-- Hero Section -->
    <section class="hero-bg min-h-screen flex items-center justify-center text-gray-200 relative overflow-hidden py-5">
        <div class="container mx-auto px-4 text-center z-10 position-relative">
            <div class="postion-absolute w-full top-0 left-0">
                <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/9f6c37a1-dd31-4f96-8aec-0feb8ac5477f.png"
                    alt="Ilustrasi bengkel modern dengan teknologi tinggi"
                    class="w-full max-w-2xl mx-auto rounded-lg shadow-lg animate-pulse border border-yellow-400" />
            </div>
            <h1 class="text-4xl md:text-6xl font-extrabold mb-4 drop-shadow-md">
                BengkelApp Pro Akan Segera Hadir!
            </h1>
            <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto drop-shadow-sm text-gray-300">
                Kami sedang mempersiapkan aplikasi terlengkap untuk mengelola bengkel
                otomotif Anda. Dengan fitur-fitur canggih seperti manajemen servis, stok
                sparepart, dan tracking pelanggan real-time, bisnis Anda akan lebih
                efisien dan menguntungkan. Peluncuran resmi:
                <span class="font-semibold text-blue-300">Januari 2025</span>.
            </p>
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 drop-shadow-sm text-gray-100">
                    Daftar untuk Notifikasi Peluncuran
                </h2>
                <form class="max-w-md mx-auto" id="subscribe-form">
                    <div class="flex flex-col md:flex-row gap-4">
                        <input type="email" id="email" placeholder="Masukkan email Anda"
                            class="flex-1 px-4 py-2 rounded-full border border-gray-600 bg-gray-800 text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            required />
                        <button type="submit"
                            class="bg-blue-600 text-gray-100 px-6 py-2 rounded-full font-semibold hover:bg-blue-700 transition">
                            Daftar
                        </button>
                    </div>
                    <p class="text-sm mt-2 text-gray-400">
                        Kami jamin privasi email Anda. Tidak ada spam!
                    </p>
                </form>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-gray-200">
                <div class="bg-gray-800 bg-opacity-50 p-4 rounded-lg border border-gray-700 drop-shadow-sm">
                    <h3 class="text-2xl font-bold" id="days">0</h3>
                    <p>Hari</p>
                </div>
                <div class="bg-gray-800 bg-opacity-50 p-4 rounded-lg border border-gray-700 drop-shadow-sm">
                    <h3 class="text-2xl font-bold" id="hours">0</h3>
                    <p>Jam</p>
                </div>
                <div class="bg-gray-800 bg-opacity-50 p-4 rounded-lg border border-gray-700 drop-shadow-sm">
                    <h3 class="text-2xl font-bold" id="minutes">0</h3>
                    <p>Menit</p>
                </div>
                <div class="bg-gray-800 bg-opacity-50 p-4 rounded-lg border border-gray-700 drop-shadow-sm">
                    <h3 class="text-2xl font-bold" id="seconds">0</h3>
                    <p>Detik</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-400">
                Hingga peluncuran resmi BengkelApp Pro
            </p>
        </div>
    </section>

    <!-- Info Section -->
    <section class="py-16 bg-gray-900 text-gray-200">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-8 drop-shadow-sm">
                Apa yang Bisa Anda Harapkan?
            </h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-6 border border-gray-700 rounded-lg drop-shadow-sm">
                    <i class="bi bi-tools text-4xl text-blue-400 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Manajemen Servis Canggih</h3>
                    <p>
                        Lacak jadwal servis, riwayat perbaikan, dan inventaris sparepart
                        secara otomatis.
                    </p>
                </div>
                <div class="p-6 border border-gray-700 rounded-lg drop-shadow-sm">
                    <i class="bi bi-people-fill text-4xl text-blue-400 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">CRM untuk Pelanggan</h3>
                    <p>
                        Simpan data pelanggan, kirim notifikasi, dan buat program loyalty
                        eksklusif.
                    </p>
                </div>
                <div class="p-6 border border-gray-700 rounded-lg drop-shadow-sm">
                    <i class="bi bi-bar-chart-line text-4xl text-blue-400 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Analitik & Laporan</h3>
                    <p>
                        Dapatkan wawasan bisnis dengan laporan real-time dan prediksi
                        pendapatan.
                    </p>
                </div>
            </div>
            <div class="mt-8">
                <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/0a77a30d-1c63-43c6-931a-5ab0b87c0e06.png"
                    alt="Conceptual wireframe of a mobile app dashboard showing graphs, car icons, user profiles, and toolbars in a clean, modern interface design"
                    class="w-full max-w-lg mx-auto rounded-lg shadow-lg border border-gray-700" />
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8 text-center drop-shadow-sm">
        <div class="container mx-auto px-4">
            <p>© 2024 BengkelApp Pro. Semua Hak Dilindungi.</p>
            <p class="mt-2">
                Tim kami sedang bekerja keras untuk menghadirkan pengalaman terbaik.
                Ikuti kami untuk update!
            </p>
            <div class="flex justify-center space-x-4 mt-4">
                <a href="#" class="hover:text-blue-400">
                    <i class="bi bi-facebook text-2xl"></i>
                </a>
                <a href="#" class="hover:text-blue-400">
                    <i class="bi bi-instagram text-2xl"></i>
                </a>
                <a href="#" class="hover:text-blue-400">
                    <i class="bi bi-twitter text-2xl"></i>
                </a>
            </div>
        </div>
    </footer>

    <script>
        // Countdown Timer (Target: January 1, 2025)
        function updateCountdown() {
            const targetDate = new Date("2025-01-01T00:00:00").getTime();
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor(
                    (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
                );
                const minutes = Math.floor(
                    (distance % (1000 * 60 * 60)) / (1000 * 60)
                );
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById("days").textContent = days;
                document.getElementById("hours").textContent = hours;
                document.getElementById("minutes").textContent = minutes;
                document.getElementById("seconds").textContent = seconds;
            } else {
                document.querySelector(".grid").innerHTML =
                    '<h2 class="col-span-4 text-2xl font-bold">Peluncuran Sekarang!</h2>';
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Simple form validation
        document
            .getElementById("subscribe-form")
            .addEventListener("submit", function(e) {
                e.preventDefault();
                const email = document.getElementById("email").value;
                if (email.includes("@") && email.includes(".")) {
                    alert("Terima kasih! Kami akan menghubungi Anda saat peluncuran.");
                    document.getElementById("email").value = "";
                } else {
                    alert("Silakan masukkan email yang valid.");
                }
            });
    </script>
</body>

</html> --}}
