{{-- Contoh manifes timbang limbah medis & B3. Data contoh, bukan data nyata. --}}
<aside class="akun__nota" aria-labelledby="nota-judul">
    <div class="nota-wrap">
        <div class="nota-wrap__lencana">
            <span class="lencana-contoh">Contoh tampilan</span>
            <span class="lencana-k3">Standar K3 Fasyankes</span>
        </div>

        <p class="nota-wrap__judul" id="nota-judul">{{ $judul }}</p>

        <div class="nota" role="img" aria-label="Contoh tampilan manifes setoran yang telah selesai diverifikasi: Medis Infeksius 2 item, Medis Benda tajam 1 item, Medis Farmasi 3 item, Medis Kemasan B3 2 item. Total 8 item, 78 poin insentif B3.">
            <div class="nota__kepala" aria-hidden="true">
                <div class="nota__kepala-atas">
                    <div class="nota__tps">TPS Kenanga · Berizin Medis</div>
                    <span class="stempel">SELESAI</span>
                </div>
                <div class="nota__kode">MNF-20261106-0001 · selesai diterima di fasilitas</div>
            </div>

            <div class="nota__daftar" aria-hidden="true">
                <div class="nota__baris">
                    <span class="nota__item">Medis · Infeksius</span>
                    <span class="penuntun"></span>
                    <span class="nota__berat">2 item</span>
                </div>
                <div class="nota__baris">
                    <span class="nota__item">Medis · Benda tajam</span>
                    <span class="penuntun"></span>
                    <span class="nota__berat">1 item</span>
                </div>
                <div class="nota__baris">
                    <span class="nota__item">Medis · Farmasi</span>
                    <span class="penuntun"></span>
                    <span class="nota__berat">3 item</span>
                </div>
                <div class="nota__baris">
                    <span class="nota__item">Medis · Kemasan B3</span>
                    <span class="penuntun"></span>
                    <span class="nota__berat">2 item</span>
                </div>

                <div class="nota__baris nota__total">
                    <span class="nota__item">Total item diterima</span>
                    <span class="penuntun"></span>
                    <span class="nota__berat">8 item</span>
                </div>
                <div class="nota__baris nota__poin">
                    <span class="nota__item">Insentif poin B3</span>
                    <span class="penuntun"></span>
                    <span class="nota__berat">+78 pts</span>
                </div>
            </div>
        </div>

        {{-- Ilustrasi relevan: 4 wadah standar limbah medis & B3 (menggantikan kolase energi hijau) --}}
        <div class="wadah-k3" aria-label="Kode wadah standar limbah medis">
            <div class="wadah-k3__item">
                <svg viewBox="0 0 28 28" aria-hidden="true" class="wadah-k3__ikon">
                    <rect x="4" y="6" width="20" height="18" rx="3" fill="#FFF8E1" stroke="#B26A00" stroke-width="1.8"/>
                    <path d="M10 6V4a4 4 0 018 0v2" stroke="#B26A00" stroke-width="1.8" fill="none"/>
                    <circle cx="14" cy="15" r="3.2" stroke="#B26A00" stroke-width="1.6" fill="none"/>
                </svg>
                <div>
                    <div class="wadah-k3__nama">Kantong Kuning</div>
                    <div class="wadah-k3__ket">Infeksius &amp; APD</div>
                </div>
            </div>

            <div class="wadah-k3__item">
                <svg viewBox="0 0 28 28" aria-hidden="true" class="wadah-k3__ikon">
                    <rect x="5" y="4" width="18" height="20" rx="2.5" fill="#FCE8E6" stroke="#B3261E" stroke-width="1.8"/>
                    <line x1="9" y1="8" x2="19" y2="8" stroke="#B3261E" stroke-width="2" stroke-linecap="round"/>
                    <path d="M14 12l4 7h-8l4-7z" stroke="#B3261E" stroke-width="1.5" fill="none" stroke-linejoin="round"/>
                </svg>
                <div>
                    <div class="wadah-k3__nama">Safety Box</div>
                    <div class="wadah-k3__ket">Jarum &amp; ampul</div>
                </div>
            </div>

            <div class="wadah-k3__item">
                <svg viewBox="0 0 28 28" aria-hidden="true" class="wadah-k3__ikon">
                    <rect x="6" y="7" width="16" height="17" rx="3" fill="#EFEBE9" stroke="#5D4037" stroke-width="1.8"/>
                    <rect x="10" y="3" width="8" height="4" rx="1" fill="#EFEBE9" stroke="#5D4037" stroke-width="1.6"/>
                    <path d="M14 12v6M11 15h6" stroke="#5D4037" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <div>
                    <div class="wadah-k3__nama">Wadah Cokelat</div>
                    <div class="wadah-k3__ket">Obat &amp; farmasi</div>
                </div>
            </div>

            <div class="wadah-k3__item">
                <svg viewBox="0 0 28 28" aria-hidden="true" class="wadah-k3__ikon">
                    <rect x="4" y="5" width="20" height="19" rx="3" fill="#E3EFE7" stroke="#133A26" stroke-width="1.8"/>
                    <path d="M9 10h10M9 14.5h10M9 19h6" stroke="#133A26" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <div>
                    <div class="wadah-k3__nama">Kontainer B3</div>
                    <div class="wadah-k3__ket">Kimia &amp; merkuri</div>
                </div>
            </div>
        </div>

        <p class="nota-wrap__ket">{!! $keterangan !!}</p>
    </div>
</aside>
