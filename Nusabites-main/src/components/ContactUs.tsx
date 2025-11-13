import { useState } from 'react';
import { ArrowLeft, Mail, MapPin, Phone, Send, Instagram, Facebook, Twitter } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import { Input } from './ui/input';
import { Textarea } from './ui/textarea';
import { Label } from './ui/label';
import { toast } from 'sonner@2.0.3';

interface ContactUsProps {
  onBack: () => void;
}

export function ContactUs({ onBack }: ContactUsProps) {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    subject: '',
    message: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log('Contact form submitted:', formData);
    toast.success('Pesan Anda telah terkirim! Kami akan segera menghubungi Anda.');
    setFormData({
      name: '',
      email: '',
      subject: '',
      message: '',
    });
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Button 
          variant="ghost" 
          onClick={onBack}
          className="mb-6"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Kembali
        </Button>

        <div className="text-center mb-12">
          <h1 className="mb-4">Hubungi Kami</h1>
          <p className="text-gray-600 max-w-2xl mx-auto">
            Ada pertanyaan, saran, atau ingin berkolaborasi? Kami senang mendengar dari Anda. 
            Jangan ragu untuk menghubungi kami melalui formulir di bawah ini.
          </p>
        </div>

        <div className="grid lg:grid-cols-3 gap-8 mb-12">
          {/* Contact Information Cards */}
          <Card className="hover:shadow-lg transition-shadow">
            <CardContent className="p-6 text-center">
              <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Mail className="w-8 h-8 text-orange-600" />
              </div>
              <h3 className="mb-2">Email</h3>
              <p className="text-gray-600 mb-2">Kirim email kepada kami</p>
              <a 
                href="mailto:info@nusabites.id" 
                className="text-orange-600 hover:text-orange-700"
              >
                info@nusabites.id
              </a>
            </CardContent>
          </Card>

          <Card className="hover:shadow-lg transition-shadow">
            <CardContent className="p-6 text-center">
              <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Phone className="w-8 h-8 text-orange-600" />
              </div>
              <h3 className="mb-2">Telepon</h3>
              <p className="text-gray-600 mb-2">Hubungi kami langsung</p>
              <a 
                href="tel:+622112345678" 
                className="text-orange-600 hover:text-orange-700"
              >
                +62 21 1234 5678
              </a>
            </CardContent>
          </Card>

          <Card className="hover:shadow-lg transition-shadow">
            <CardContent className="p-6 text-center">
              <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <MapPin className="w-8 h-8 text-orange-600" />
              </div>
              <h3 className="mb-2">Alamat</h3>
              <p className="text-gray-600 mb-2">Kunjungi kantor kami</p>
              <p className="text-orange-600">
                Jakarta, Indonesia
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="grid lg:grid-cols-2 gap-8">
          {/* Contact Form */}
          <Card>
            <CardContent className="p-8">
              <h2 className="mb-6">Kirim Pesan</h2>
              <form onSubmit={handleSubmit} className="space-y-6">
                <div>
                  <Label htmlFor="name">Nama Lengkap *</Label>
                  <Input
                    id="name"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    placeholder="Masukkan nama Anda"
                    required
                    className="mt-2"
                  />
                </div>

                <div>
                  <Label htmlFor="email">Email *</Label>
                  <Input
                    id="email"
                    name="email"
                    type="email"
                    value={formData.email}
                    onChange={handleChange}
                    placeholder="nama@email.com"
                    required
                    className="mt-2"
                  />
                </div>

                <div>
                  <Label htmlFor="subject">Subjek *</Label>
                  <Input
                    id="subject"
                    name="subject"
                    value={formData.subject}
                    onChange={handleChange}
                    placeholder="Topik pesan Anda"
                    required
                    className="mt-2"
                  />
                </div>

                <div>
                  <Label htmlFor="message">Pesan *</Label>
                  <Textarea
                    id="message"
                    name="message"
                    value={formData.message}
                    onChange={handleChange}
                    placeholder="Tulis pesan Anda di sini..."
                    rows={6}
                    required
                    className="mt-2"
                  />
                </div>

                <Button 
                  type="submit" 
                  className="w-full bg-orange-600 hover:bg-orange-700"
                >
                  <Send className="w-4 h-4 mr-2" />
                  Kirim Pesan
                </Button>
              </form>
            </CardContent>
          </Card>

          {/* Additional Info & Social Media */}
          <div className="space-y-6">
            <Card>
              <CardContent className="p-8">
                <h2 className="mb-6">Jam Operasional</h2>
                <div className="space-y-3 text-gray-600">
                  <div className="flex justify-between">
                    <span>Senin - Jumat</span>
                    <span>09:00 - 18:00 WIB</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Sabtu</span>
                    <span>09:00 - 14:00 WIB</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Minggu</span>
                    <span className="text-red-600">Tutup</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-8">
                <h2 className="mb-6">Ikuti Kami</h2>
                <p className="text-gray-600 mb-6">
                  Dapatkan update resep terbaru dan tips memasak di media sosial kami.
                </p>
                <div className="flex gap-3">
                  <Button
                    variant="outline"
                    size="icon"
                    className="hover:bg-orange-50 hover:border-orange-500"
                    onClick={() => window.open('https://instagram.com', '_blank')}
                  >
                    <Instagram className="w-5 h-5" />
                  </Button>
                  <Button
                    variant="outline"
                    size="icon"
                    className="hover:bg-orange-50 hover:border-orange-500"
                    onClick={() => window.open('https://facebook.com', '_blank')}
                  >
                    <Facebook className="w-5 h-5" />
                  </Button>
                  <Button
                    variant="outline"
                    size="icon"
                    className="hover:bg-orange-50 hover:border-orange-500"
                    onClick={() => window.open('https://twitter.com', '_blank')}
                  >
                    <Twitter className="w-5 h-5" />
                  </Button>
                </div>
              </CardContent>
            </Card>

            <Card className="bg-gradient-to-br from-orange-50 to-red-50 border-orange-200">
              <CardContent className="p-8">
                <h3 className="mb-3">Butuh Bantuan Cepat?</h3>
                <p className="text-gray-600 mb-4">
                  Lihat halaman FAQ kami untuk jawaban dari pertanyaan yang sering diajukan.
                </p>
                <Button 
                  variant="outline" 
                  className="border-orange-500 text-orange-700 hover:bg-orange-100"
                >
                  Lihat FAQ
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* FAQ Preview */}
        <Card className="mt-8">
          <CardContent className="p-8">
            <h2 className="mb-6">Pertanyaan Umum</h2>
            <div className="grid md:grid-cols-2 gap-6">
              <div>
                <h3 className="mb-2">Bagaimana cara menambah resep?</h3>
                <p className="text-gray-600">
                  Klik tombol "Tambah Resep" di header, lalu isi formulir dengan detail resep Anda 
                  termasuk bahan dan langkah-langkah.
                </p>
              </div>
              <div>
                <h3 className="mb-2">Apakah gratis untuk bergabung?</h3>
                <p className="text-gray-600">
                  Ya, Nusa Bites sepenuhnya gratis untuk semua pengguna. Anda dapat melihat, 
                  menambahkan, dan membagikan resep tanpa biaya.
                </p>
              </div>
              <div>
                <h3 className="mb-2">Bagaimana cara memberikan review?</h3>
                <p className="text-gray-600">
                  Buka halaman detail resep, scroll ke bagian review, dan klik "Tulis Review" 
                  untuk berbagi pengalaman Anda.
                </p>
              </div>
              <div>
                <h3 className="mb-2">Bisakah saya menghapus resep saya?</h3>
                <p className="text-gray-600">
                  Ya, di halaman profil Anda akan menemukan semua resep yang Anda buat 
                  dengan opsi untuk mengedit atau menghapusnya.
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
