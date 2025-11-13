import { useState } from 'react';
import { ChefHat, Mail, Lock, User, Eye, EyeOff, Check } from 'lucide-react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Card, CardContent } from './ui/card';
import { ImageWithFallback } from './figma/ImageWithFallback';
import { toast } from 'sonner@2.0.3';

interface RegisterProps {
  onRegister: (email: string, name: string) => void;
  onSwitchToLogin: () => void;
  onBack: () => void;
}

export function Register({ onRegister, onSwitchToLogin, onBack }: RegisterProps) {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
  });
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [agreeToTerms, setAgreeToTerms] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const validateForm = () => {
    if (!formData.name || !formData.email || !formData.password || !formData.confirmPassword) {
      toast.error('Semua field harus diisi!');
      return false;
    }

    if (formData.password.length < 6) {
      toast.error('Password minimal 6 karakter!');
      return false;
    }

    if (formData.password !== formData.confirmPassword) {
      toast.error('Password tidak cocok!');
      return false;
    }

    if (!agreeToTerms) {
      toast.error('Anda harus menyetujui syarat dan ketentuan!');
      return false;
    }

    return true;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    setIsLoading(true);

    // Simulate API call
    setTimeout(() => {
      onRegister(formData.email, formData.name);
      toast.success('Registrasi berhasil! Selamat bergabung di Nusa Bites.');
      setIsLoading(false);
    }, 1000);
  };

  const passwordStrength = () => {
    const password = formData.password;
    if (!password) return 0;
    
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    return Math.min(strength, 3);
  };

  const strengthColors = ['bg-red-500', 'bg-yellow-500', 'bg-green-500'];
  const strengthLabels = ['Lemah', 'Sedang', 'Kuat'];
  const strength = passwordStrength();

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-5xl w-full grid lg:grid-cols-2 gap-8 items-center">
        {/* Left side - Branding */}
        <div className="hidden lg:block">
          <div className="text-center mb-8">
            <button 
              onClick={onBack}
              className="inline-flex items-center gap-3 mb-6"
            >
              <div className="bg-gradient-to-br from-orange-500 to-red-600 p-4 rounded-2xl">
                <ChefHat className="w-12 h-12 text-white" />
              </div>
            </button>
            <h1 className="mb-4">Bergabung dengan Nusa Bites</h1>
            <p className="text-gray-600">
              Mulai perjalanan kuliner Anda dan berbagi resep masakan nusantara favorit
            </p>
          </div>
          
          <div className="relative h-96 rounded-2xl overflow-hidden shadow-lg">
            <ImageWithFallback
              src="https://images.unsplash.com/photo-1609847381390-2d71ed074efc?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwZm9vZCUyMGNvb2tpbmd8ZW58MXx8fHwxNzYyNDgyOTA4fDA&ixlib=rb-4.1.0&q=80&w=1080"
              alt="Indonesian Cooking"
              className="w-full h-full object-cover"
            />
          </div>

          <Card className="mt-6 bg-gradient-to-br from-orange-50 to-red-50 border-orange-200">
            <CardContent className="p-6">
              <h3 className="mb-4">Keuntungan Bergabung</h3>
              <ul className="space-y-3">
                <li className="flex items-start gap-2">
                  <Check className="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" />
                  <span className="text-gray-700">Akses ke ribuan resep nusantara gratis</span>
                </li>
                <li className="flex items-start gap-2">
                  <Check className="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" />
                  <span className="text-gray-700">Bagikan resep favorit Anda</span>
                </li>
                <li className="flex items-start gap-2">
                  <Check className="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" />
                  <span className="text-gray-700">Simpan resep yang Anda sukai</span>
                </li>
                <li className="flex items-start gap-2">
                  <Check className="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" />
                  <span className="text-gray-700">Bergabung dengan komunitas pecinta kuliner</span>
                </li>
              </ul>
            </CardContent>
          </Card>
        </div>

        {/* Right side - Register Form */}
        <Card className="shadow-xl">
          <CardContent className="p-8">
            {/* Mobile Logo */}
            <div className="lg:hidden text-center mb-6">
              <button 
                onClick={onBack}
                className="inline-flex items-center gap-2 mb-4"
              >
                <div className="bg-gradient-to-br from-orange-500 to-red-600 p-3 rounded-xl">
                  <ChefHat className="w-8 h-8 text-white" />
                </div>
                <span className="text-orange-600">Nusa Bites</span>
              </button>
            </div>

            <div className="mb-8">
              <h2 className="mb-2">Buat Akun Baru</h2>
              <p className="text-gray-600">
                Sudah punya akun?{' '}
                <button
                  onClick={onSwitchToLogin}
                  className="text-orange-600 hover:text-orange-700"
                >
                  Masuk di sini
                </button>
              </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-5">
              <div>
                <Label htmlFor="name">Nama Lengkap</Label>
                <div className="relative mt-2">
                  <User className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                  <Input
                    id="name"
                    name="name"
                    type="text"
                    value={formData.name}
                    onChange={handleChange}
                    placeholder="Masukkan nama lengkap"
                    className="pl-10"
                    required
                  />
                </div>
              </div>

              <div>
                <Label htmlFor="email">Email</Label>
                <div className="relative mt-2">
                  <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                  <Input
                    id="email"
                    name="email"
                    type="email"
                    value={formData.email}
                    onChange={handleChange}
                    placeholder="nama@email.com"
                    className="pl-10"
                    required
                  />
                </div>
              </div>

              <div>
                <Label htmlFor="password">Password</Label>
                <div className="relative mt-2">
                  <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                  <Input
                    id="password"
                    name="password"
                    type={showPassword ? 'text' : 'password'}
                    value={formData.password}
                    onChange={handleChange}
                    placeholder="Minimal 6 karakter"
                    className="pl-10 pr-10"
                    required
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    {showPassword ? (
                      <EyeOff className="w-5 h-5" />
                    ) : (
                      <Eye className="w-5 h-5" />
                    )}
                  </button>
                </div>
                
                {formData.password && (
                  <div className="mt-2">
                    <div className="flex gap-1 mb-1">
                      {[0, 1, 2].map((i) => (
                        <div
                          key={i}
                          className={`h-1 flex-1 rounded ${
                            i < strength ? strengthColors[strength - 1] : 'bg-gray-200'
                          }`}
                        />
                      ))}
                    </div>
                    <p className={`text-sm ${
                      strength === 0 ? 'text-red-600' : 
                      strength === 1 ? 'text-yellow-600' : 
                      strength === 2 ? 'text-yellow-600' : 
                      'text-green-600'
                    }`}>
                      {strength > 0 ? `Kekuatan: ${strengthLabels[strength - 1]}` : ''}
                    </p>
                  </div>
                )}
              </div>

              <div>
                <Label htmlFor="confirmPassword">Konfirmasi Password</Label>
                <div className="relative mt-2">
                  <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                  <Input
                    id="confirmPassword"
                    name="confirmPassword"
                    type={showConfirmPassword ? 'text' : 'password'}
                    value={formData.confirmPassword}
                    onChange={handleChange}
                    placeholder="Masukkan ulang password"
                    className="pl-10 pr-10"
                    required
                  />
                  <button
                    type="button"
                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                    className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    {showConfirmPassword ? (
                      <EyeOff className="w-5 h-5" />
                    ) : (
                      <Eye className="w-5 h-5" />
                    )}
                  </button>
                </div>
                {formData.confirmPassword && formData.password !== formData.confirmPassword && (
                  <p className="text-sm text-red-600 mt-1">Password tidak cocok</p>
                )}
              </div>

              <div className="flex items-start gap-2">
                <input
                  type="checkbox"
                  id="terms"
                  checked={agreeToTerms}
                  onChange={(e) => setAgreeToTerms(e.target.checked)}
                  className="rounded border-gray-300 mt-1"
                />
                <label htmlFor="terms" className="text-gray-700 cursor-pointer">
                  Saya menyetujui{' '}
                  <button type="button" className="text-orange-600 hover:text-orange-700">
                    Syarat dan Ketentuan
                  </button>{' '}
                  serta{' '}
                  <button type="button" className="text-orange-600 hover:text-orange-700">
                    Kebijakan Privasi
                  </button>
                </label>
              </div>

              <Button
                type="submit"
                className="w-full bg-orange-600 hover:bg-orange-700"
                disabled={isLoading}
              >
                {isLoading ? 'Memproses...' : 'Daftar Sekarang'}
              </Button>
            </form>

            <div className="mt-6">
              <div className="relative">
                <div className="absolute inset-0 flex items-center">
                  <div className="w-full border-t border-gray-300" />
                </div>
                <div className="relative flex justify-center">
                  <span className="px-4 bg-white text-gray-500">Atau daftar dengan</span>
                </div>
              </div>

              <div className="mt-6 grid grid-cols-2 gap-3">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => toast.info('Fitur registrasi dengan Google segera hadir!')}
                >
                  <svg className="w-5 h-5 mr-2" viewBox="0 0 24 24">
                    <path
                      fill="currentColor"
                      d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                    />
                    <path
                      fill="currentColor"
                      d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                    />
                    <path
                      fill="currentColor"
                      d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                    />
                    <path
                      fill="currentColor"
                      d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                    />
                  </svg>
                  Google
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => toast.info('Fitur registrasi dengan Facebook segera hadir!')}
                >
                  <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                  </svg>
                  Facebook
                </Button>
              </div>
            </div>

            <p className="mt-6 text-center text-gray-600">
              <button
                onClick={onBack}
                className="text-orange-600 hover:text-orange-700"
              >
                Kembali ke beranda
              </button>
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
