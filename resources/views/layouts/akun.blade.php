<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · Kemedcar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@75..100,600..800&family=Public+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/akun.css') }}">
</head>
<body>
    @yield('body')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle lihat/sembunyikan kata sandi
            document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var targetId = btn.getAttribute('data-toggle-password');
                    var input = document.getElementById(targetId);
                    if (!input) return;

                    var sedangTerlihat = input.getAttribute('type') === 'text';
                    input.setAttribute('type', sedangTerlihat ? 'password' : 'text');
                    btn.setAttribute('aria-pressed', sedangTerlihat ? 'false' : 'true');
                    btn.setAttribute('aria-label', sedangTerlihat ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');

                    var teks = btn.querySelector('.sandi-lihat__teks');
                    if (teks) {
                        teks.textContent = sedangTerlihat ? 'Lihat' : 'Sembunyikan';
                    }
                });
            });

            // Cegah klik ganda saat form dikirim
            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    var btn = form.querySelector('button[type="submit"]');
                    if (btn && !btn.disabled) {
                        btn.disabled = true;
                        btn.setAttribute('aria-busy', 'true');
                        btn.textContent = 'Memproses…';
                    }
                });
            });

            // Dialog bantuan lupa kata sandi
            var bukaLupa = document.getElementById('buka-lupa-sandi');
            var dialogLupa = document.getElementById('dialog-lupa-sandi');
            var tutupLupa = document.getElementById('tutup-lupa-sandi');

            if (bukaLupa && dialogLupa) {
                bukaLupa.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (typeof dialogLupa.showModal === 'function') {
                        dialogLupa.showModal();
                    } else {
                        dialogLupa.setAttribute('open', '');
                    }
                });

                if (tutupLupa) {
                    tutupLupa.addEventListener('click', function () {
                        if (typeof dialogLupa.close === 'function') {
                            dialogLupa.close();
                        } else {
                            dialogLupa.removeAttribute('open');
                        }
                        bukaLupa.focus();
                    });
                }

                dialogLupa.addEventListener('click', function (e) {
                    if (e.target === dialogLupa) {
                        if (typeof dialogLupa.close === 'function') {
                            dialogLupa.close();
                        } else {
                            dialogLupa.removeAttribute('open');
                        }
                        bukaLupa.focus();
                    }
                });
            }
        });
    </script>
</body>
</html>
