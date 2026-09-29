# Membuat fungsi ucapan
def ucapan():
    print("selamat pagi")
    print("selamat siang")
    print("selamat sore")
    print("selamat malam")
    
    # Fungsi hai berada di dalam ucapan
    def hai():
        print("hai dunia")
        print('halo dunia')
        print('hello world')
    
    # Memanggil hai() di dalam ucapan
    hai()

# Memanggil fungsi ucapan utama dari luar
ucapan()