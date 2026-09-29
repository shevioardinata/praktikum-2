# Fungsi untuk mengonversi Celsius ke Reamur
def celsius_ke_reamur(celsius):
    return celsius * 0.8

# Fungsi untuk mengonversi Celsius ke Fahrenheit
def celsius_ke_fahrenheit(celsius):
    return (celsius * 1.8) + 32

# Fungsi untuk mengonversi Celsius ke Kelvin
def celsius_ke_kelvin(celsius):
    return celsius + 273.15

# Program Utama
print("=== PROGRAM KONVERSI SUHU CELSIUS ===")
try:
    celsius = float(input("Masukkan suhu dalam Celsius: "))
    
    # Memanggil fungsi dan menyimpan hasil
    reamur = celsius_ke_reamur(celsius)
    fahrenheit = celsius_ke_fahrenheit(celsius)
    kelvin = celsius_ke_kelvin(celsius)
    
    # Menampilkan hasil
    print(f"\nHasil Konversi dari {celsius}°C:")
    print(f"-> Reamur     = {reamur}°R")
    print(f"-> Fahrenheit = {fahrenheit}°F")
    print(f"-> Kelvin     = {kelvin}K")

except ValueError:
    print("Error: Harap masukkan angka yang valid!")