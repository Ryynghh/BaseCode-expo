# Gunakan image dasar yang sama
FROM jasonrivers/nagios:latest

# Update dan install plugin MySQL secara otomatis saat image dibuat
RUN apt-get update && apt-get install -y monitoring-plugins-standard

# (Opsional) Buat symlink agar sesuai dengan struktur folder default Nagios
# Ini berjaga-jaga jika di masa depan Anda ingin pakai path default
RUN ln -s /usr/lib/nagios/plugins/check_mysql /opt/nagios/libexec/check_mysql