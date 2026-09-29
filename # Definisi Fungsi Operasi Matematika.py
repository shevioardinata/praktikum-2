# Definisi Fungsi Operasi Matematika
def tambah(a, b):
    return a + b

def kurang(a, b):
    return a - b

def kali(a, b):
    return a * b

def bagi(a, b):
    if b == 0:
        return "Error: Pembagian dengan nol tidak diperbolehkan!"
    return a / b

# Program Utama
print("=== KALKULATOR SEDERHANA ===")
print("Pilih Operasi:")
print("1. Penjumlahan (+)")
print("2. Pengurangan (-)")
print("3. Perkalian (*)")
print("4. Pembagian (/)")

try:
    pilihan = input("Masukkan pilihan (1/2/3/4): ")

    if pilihan in ['1', '2', '3', '4']:
        angka1 = float(input("Masukkan angka pertama: "))
        angka2 = float(input("Masukkan angka kedua: "))
        
        print("\nHasil:")
        if pilihan == '1':
            print(f"{angka1} + {angka2} = {tambah(angka1, angka2)}")
        elif pilihan == '2':
            print(f"{angka1} - {angka2} = {kurang(angka1, angka2)}")
        elif pilihan == '3':
            print(f"{angka1} * {angka2} = {kali(angka1, angka2)}")
        elif pilihan == '4':
            print(f"{angka1} / {angka2} = {bagi(angka1, angka2)}")
    else:
        print("Pilihan operasi tidak valid!")

except ValueError:
    print("Error: Harap masukkan angka yang valid!")