</div> <!-- /container -->
        </div> <!-- /main -->
    </div> <!-- /wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Kenar çubuğu açma/kapama
        $("#sidebarToggle").click(function(e) {
            e.preventDefault();
            $("#sidebar").toggleClass("active");
            $(".main").toggleClass("active");
            $(".navbar").toggleClass("active");
        });
        
        // Mobil görünümde kenar çubuğunu kapat
        $("#closeSidebar").click(function(e) {
            e.preventDefault();
            $("#sidebar").removeClass("active");
            $(".main").removeClass("active");
            $(".navbar").removeClass("active");
        });
        
        // DataTables başlatma - özel başlatılmış tablolar için kontrol et
        if ($('.datatable').length && !$('.datatable').hasClass('dataTable')) {
            try {
                $('.datatable').each(function() {
                    var tableId = $(this).attr('id');
                    if (!tableId || (tableId && !window['dt_' + tableId + '_initialized'])) {
                        $(this).DataTable({
                            language: {
                                url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/tr.json",
                                paginate: {
                                    previous: "Önceki",
                                    next: "Sonraki"
                                },
                                search: "Ara:",
                                info: "Toplam _TOTAL_ kayıttan _START_ ile _END_ arası gösteriliyor",
                                lengthMenu: "Sayfa başına _MENU_ kayıt göster",
                                zeroRecords: "Kayıt bulunamadı",
                                infoEmpty: "Kayıt bulunamadı",
                                infoFiltered: "(_MAX_ kayıt arasından filtrelendi)"
                            },
                            responsive: true,
                            columnDefs: [
                                { orderable: false, targets: -1 }
                            ]
                        });
                    }
                });
            } catch (e) {
                console.error('DataTables başlatma hatası:', e);
            }
        }
        
        // İpucu özelliğini başlat
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Animasyonları başlat
        const animateElements = document.querySelectorAll('.animate');
        if (animateElements.length) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                    }
                });
            }, { threshold: 0.1 });
            
            animateElements.forEach(element => {
                observer.observe(element);
            });
        }
        
        // Kullanıcının konumunu al
        function getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Konum başarıyla alındı
                        var lat = position.coords.latitude;
                        var lon = position.coords.longitude;
                        
                        // Konsola konum bilgisini yazdır
                        console.log("Konum alındı: ", lat, lon);
                        
                        // Konum bilgisini sunucuya gönder
                        $.ajax({
                            url: 'location_handler.php',
                            type: 'POST',
                            data: {
                                lat: lat,
                                lon: lon,
                                action: 'save_location'
                            },
                            success: function(response) {
                                console.log("Konum kaydedildi: ", response);
                                // Sayfa yenilendiğinde yeni konum bilgisi görünür olacak
                                if (response.indexOf('success') !== -1) {
                                    // Başarılı mesajı görününce sayfayı yenile
                                    setTimeout(function() {
                                        location.reload();
                                    }, 500);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Konum kaydedilemedi: ", error);
                            }
                        });
                    },
                    function(error) {
                        // Konum alma hatası
                        console.error("Konum alınamadı: ", error.message);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            } else {
                console.error("Tarayıcınız konum özelliğini desteklemiyor.");
            }
        }
        
        // Konum bilgisini al
        <?php if(!isset($_SESSION['user_location'])): ?>
        // Sadece konum bilgisi yoksa çalıştır
        getUserLocation();
        <?php endif; ?>
    });
    </script>
</body>
</html>
