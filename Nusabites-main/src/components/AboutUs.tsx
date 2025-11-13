import { ArrowLeft, ChefHat, Heart, Users, Target } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import { ImageWithFallback } from './figma/ImageWithFallback';

interface AboutUsProps {
  onBack: () => void;
}

export function AboutUs({ onBack }: AboutUsProps) {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Button 
          variant="ghost" 
          onClick={onBack}
          className="mb-6"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Kembali
        </Button>

        {/* Hero Section */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center gap-3 mb-6">
            <div className="bg-gradient-to-br from-orange-500 to-red-600 p-4 rounded-2xl">
              <ChefHat className="w-12 h-12 text-white" />
            </div>
          </div>
          <h1 className="mb-4">Tentang Nusa Bites</h1>
          <p className="text-gray-600 max-w-2xl mx-auto">
            Platform berbagi resep masakan nusantara yang menghubungkan pecinta kuliner 
            di seluruh Indonesia untuk melestarikan dan berbagi kekayaan cita rasa tradisional.
          </p>
        </div>

        {/* Hero Image */}
        <div className="relative h-96 rounded-2xl overflow-hidden mb-16 shadow-lg">
          <ImageWithFallback
            src="https://images.unsplash.com/photo-1752760023161-c2b5d8edd1a3?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwdHJhZGl0aW9uYWwlMjBjb29raW5nJTIwa2l0Y2hlbnxlbnwxfHx8fDE3NjI0ODM3OTB8MA&ixlib=rb-4.1.0&q=80&w=1080"
            alt="Indonesian Cooking"
            className="w-full h-full object-cover"
          />
        </div>

        {/* Mission & Vision */}
        <div className="grid md:grid-cols-3 gap-6 mb-16">
          <Card className="border-t-4 border-orange-500">
            <CardContent className="p-6 text-center">
              <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Target className="w-8 h-8 text-orange-600" />
              </div>
              <h3 className="mb-3">Misi Kami</h3>
              <p className="text-gray-600">
                Melestarikan resep tradisional nusantara dan memudahkan setiap orang 
                untuk memasak hidangan Indonesia yang autentik.
              </p>
            </CardContent>
          </Card>

          <Card className="border-t-4 border-red-500">
            <CardContent className="p-6 text-center">
              <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Heart className="w-8 h-8 text-red-600" />
              </div>
              <h3 className="mb-3">Passion</h3>
              <p className="text-gray-600">
                Kami percaya bahwa makanan adalah jembatan yang menghubungkan 
                generasi dan menyatukan keluarga.
              </p>
            </CardContent>
          </Card>

          <Card className="border-t-4 border-yellow-500">
            <CardContent className="p-6 text-center">
              <div className="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Users className="w-8 h-8 text-yellow-600" />
              </div>
              <h3 className="mb-3">Komunitas</h3>
              <p className="text-gray-600">
                Membangun komunitas pecinta kuliner yang saling berbagi, 
                belajar, dan tumbuh bersama.
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Story Section */}
        <Card className="mb-16">
          <CardContent className="p-8">
            <h2 className="mb-6">Cerita Kami</h2>
            <div className="space-y-4 text-gray-600">
              <p>
                Nusa Bites lahir dari kecintaan kami terhadap kekayaan kuliner Indonesia. 
                Kami menyadari bahwa banyak resep tradisional yang mulai terlupakan seiring 
                berjalannya waktu, dan kami ingin mengubah itu.
              </p>
              <p>
                Platform ini diciptakan untuk menjadi rumah bagi semua resep nusantara, 
                dari Sabang sampai Merauke. Dari resep turun-temurun nenek moyang hingga 
                inovasi modern yang tetap mempertahankan cita rasa tradisional.
              </p>
              <p>
                Setiap resep yang dibagikan di Nusa Bites adalah bagian dari warisan budaya 
                kita. Melalui platform ini, kami berharap dapat melestarikan kekayaan kuliner 
                Indonesia dan membuatnya mudah diakses oleh siapa saja, di mana saja.
              </p>
            </div>
          </CardContent>
        </Card>

        {/* Values */}
        <div className="mb-16">
          <h2 className="text-center mb-8">Nilai-Nilai Kami</h2>
          <div className="grid md:grid-cols-2 gap-6">
            <Card>
              <CardContent className="p-6">
                <h3 className="mb-3">🌟 Kualitas</h3>
                <p className="text-gray-600">
                  Setiap resep yang kami kurasi dipastikan mudah diikuti dan menghasilkan 
                  hidangan yang lezat.
                </p>
              </CardContent>
            </Card>
            
            <Card>
              <CardContent className="p-6">
                <h3 className="mb-3">🤝 Kebersamaan</h3>
                <p className="text-gray-600">
                  Kami membangun platform yang inklusif di mana setiap orang dapat berbagi 
                  dan belajar.
                </p>
              </CardContent>
            </Card>
            
            <Card>
              <CardContent className="p-6">
                <h3 className="mb-3">🏛️ Warisan Budaya</h3>
                <p className="text-gray-600">
                  Melestarikan resep tradisional dan cerita di balik setiap hidangan nusantara.
                </p>
              </CardContent>
            </Card>
            
            <Card>
              <CardContent className="p-6">
                <h3 className="mb-3">💡 Inovasi</h3>
                <p className="text-gray-600">
                  Mendorong kreativitas dalam memasak sambil menghormati akar tradisional.
                </p>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Team Image */}
        <Card className="overflow-hidden">
          <div className="relative h-80">
            <ImageWithFallback
              src="https://images.unsplash.com/photo-1578366941741-9e517759c620?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx0ZWFtJTIwY29sbGFib3JhdGlvbiUyMGNvb2tpbmd8ZW58MXx8fHwxNzYyNDgzNzkwfDA&ixlib=rb-4.1.0&q=80&w=1080"
              alt="Team Collaboration"
              className="w-full h-full object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
              <div className="p-8 text-white">
                <h2 className="mb-2 text-white">Bergabunglah dengan Kami</h2>
                <p className="text-white/90 mb-4">
                  Mari bersama-sama melestarikan dan merayakan kekayaan kuliner nusantara.
                </p>
                <Button 
                  onClick={onBack}
                  className="bg-orange-600 hover:bg-orange-700"
                >
                  Mulai Berbagi Resep
                </Button>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>
  );
}
