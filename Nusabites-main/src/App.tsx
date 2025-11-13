import { useState, useMemo } from 'react';
import { Header } from './components/Header';
import { FilterSidebar, Filters } from './components/FilterSidebar';
import { RecipeList } from './components/RecipeList';
import { RecipeDetail, RecipeDetailData } from './components/RecipeDetail';
import { AddRecipeForm } from './components/AddRecipeForm';
import { EditRecipeForm } from './components/EditRecipeForm';
import { UserProfile } from './components/UserProfile';
import { AboutUs } from './components/AboutUs';
import { ContactUs } from './components/ContactUs';
import { Login } from './components/Login';
import { Register } from './components/Register';
import { AdminDashboard } from './components/AdminDashboard';
import { Recipe } from './components/RecipeCard';
import { Button } from './components/ui/button';
import { Sheet, SheetContent, SheetTrigger } from './components/ui/sheet';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from './components/ui/alert-dialog';
import { SlidersHorizontal } from 'lucide-react';
import { Toaster } from './components/ui/sonner';
import { toast } from 'sonner@2.0.3';

interface User {
  name: string;
  email: string;
  role: 'user' | 'admin';
}

// Mock data untuk resep
const MOCK_RECIPES: Recipe[] = [
  {
    id: '1',
    title: 'Nasi Goreng Spesial',
    image: 'https://images.unsplash.com/photo-1680674814945-7945d913319c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwbmFzaSUyMGdvcmVuZ3xlbnwxfHx8fDE3NjI0NzgwMzd8MA&ixlib=rb-4.1.0&q=80&w=1080',
    author: 'Siti Nurhaliza',
    authorAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Siti',
    cookingTime: '30 menit',
    category: 'Makanan Utama',
    region: 'Jawa',
    rating: 4.8,
    reviewCount: 124,
    description: 'Nasi goreng khas Indonesia dengan bumbu rempah yang kaya dan telur mata sapi.',
  },
  {
    id: '2',
    title: 'Rendang Daging Sapi',
    image: 'https://images.unsplash.com/photo-1620700668269-d3ad2a88f27e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwcmVuZGFuZ3xlbnwxfHx8fDE3NjI0ODI5MDZ8MA&ixlib=rb-4.1.0&q=80&w=1080',
    author: 'Budi Santoso',
    authorAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Budi',
    cookingTime: '2 jam',
    category: 'Makanan Utama',
    region: 'Sumatera',
    rating: 4.9,
    reviewCount: 256,
    description: 'Rendang autentik Padang dengan daging empuk dan bumbu yang meresap sempurna.',
  },
  {
    id: '3',
    title: 'Sate Ayam Madura',
    image: 'https://images.unsplash.com/photo-1636301175218-6994458a4b0a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwc2F0ZSUyMHNhdGF5fGVufDF8fHx8MTc2MjQ4MjkwNnww&ixlib=rb-4.1.0&q=80&w=1080',
    author: 'Ahmad Wijaya',
    authorAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Ahmad',
    cookingTime: '45 menit',
    category: 'Makanan Utama',
    region: 'Jawa',
    rating: 4.7,
    reviewCount: 89,
    description: 'Sate ayam dengan bumbu kacang yang gurih dan manis khas Madura.',
  },
  {
    id: '4',
    title: 'Gado-Gado Jakarta',
    image: 'https://images.unsplash.com/photo-1707269561481-a4a0370a980a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwZ2FkbyUyMGdhZG98ZW58MXx8fHwxNzYyNDgyOTA3fDA&ixlib=rb-4.1.0&q=80&w=1080',
    author: 'Dewi Lestari',
    authorAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Dewi',
    cookingTime: '25 menit',
    category: 'Makanan Utama',
    region: 'Jawa',
    rating: 4.6,
    reviewCount: 67,
    description: 'Salad sayuran segar dengan saus kacang yang creamy dan lezat.',
  },
  {
    id: '5',
    title: 'Bakso Malang',
    image: 'https://images.unsplash.com/photo-1696884422000-0fcd1f115c54?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwYmFrc298ZW58MXx8fHwxNzYyNDgyOTA3fDA&ixlib=rb-4.1.0&q=80&w=1080',
    author: 'Rudi Hartono',
    authorAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Rudi',
    cookingTime: '1 jam',
    category: 'Makanan Utama',
    region: 'Jawa',
    rating: 4.8,
    reviewCount: 156,
    description: 'Bakso kenyal dengan kuah kaldu yang gurih dan tahu goreng crispy.',
  },
  {
    id: '6',
    title: 'Martabak Manis',
    image: 'https://images.unsplash.com/photo-1706922122195-a1d670210618?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwbWFydGFiYWt8ZW58MXx8fHwxNzYyNDgyOTA4fDA&ixlib=rb-4.1.0&q=80&w=1080',
    author: 'Linda Wijaya',
    authorAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Linda',
    cookingTime: '40 menit',
    category: 'Camilan',
    region: 'Sumatera',
    rating: 4.5,
    reviewCount: 92,
    description: 'Martabak tebal dengan topping coklat, keju, dan kacang yang melimpah.',
  },
  {
    id: '7',
    title: 'Es Teler Segar',
    image: 'https://images.unsplash.com/photo-1649090909560-6c71b1aeaa7d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwZXMlMjB0ZWxlcnxlbnwxfHx8fDE3NjI0ODI5MDh8MA&ixlib=rb-4.1.0&q=80&w=1080',
    author: 'Andi Pratama',
    authorAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Andi',
    cookingTime: '15 menit',
    category: 'Minuman',
    region: 'Jawa',
    rating: 4.7,
    reviewCount: 78,
    description: 'Minuman segar dengan campuran buah alpukat, kelapa muda, dan santan.',
  },
  {
    id: '8',
    title: 'Soto Ayam Lamongan',
    image: 'https://images.unsplash.com/photo-1609847381390-2d71ed074efc?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwZm9vZCUyMGNvb2tpbmd8ZW58MXx8fHwxNzYyNDgyOTA4fDA&ixlib=rb-4.1.0&q=80&w=1080',
    author: 'Yuni Astuti',
    authorAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Yuni',
    cookingTime: '50 menit',
    category: 'Makanan Utama',
    region: 'Jawa',
    rating: 4.6,
    reviewCount: 134,
    description: 'Soto ayam dengan kuah bening yang segar dan bumbu koya yang harum.',
  },
];

// Extended recipe data untuk detail page
const getRecipeDetail = (id: string): RecipeDetailData | null => {
  const recipe = MOCK_RECIPES.find(r => r.id === id);
  if (!recipe) return null;

  return {
    ...recipe,
    ingredients: [
      '500g daging ayam fillet',
      '3 siung bawang putih',
      '5 siung bawang merah',
      '2 sdm kecap manis',
      '1 sdt garam',
      '1/2 sdt merica',
      '2 sdm minyak goreng',
      'Daun bawang secukupnya',
    ],
    steps: [
      'Potong daging ayam menjadi ukuran sesuai selera, lalu marinasi dengan garam dan merica selama 15 menit.',
      'Haluskan bawang putih dan bawang merah, kemudian tumis hingga harum.',
      'Masukkan daging ayam yang sudah dimarinasi, masak hingga berubah warna.',
      'Tambahkan kecap manis, aduk rata dan masak hingga bumbu meresap sempurna.',
      'Tambahkan daun bawang yang sudah diiris, aduk sebentar lalu angkat.',
      'Sajikan selagi hangat dengan nasi putih atau lontong.',
    ],
    reviews: [
      {
        id: 'r1',
        author: 'Rina Susanti',
        avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Rina',
        rating: 5,
        comment: 'Resepnya sangat mudah diikuti dan hasilnya enak sekali! Keluarga saya sangat suka.',
        date: '2 hari yang lalu',
      },
      {
        id: 'r2',
        author: 'Doni Saputra',
        avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Doni',
        rating: 4,
        comment: 'Enak, tapi saya tambahkan sedikit cabai untuk rasa lebih pedas.',
        date: '5 hari yang lalu',
      },
      {
        id: 'r3',
        author: 'Maya Sari',
        avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Maya',
        rating: 5,
        comment: 'Sempurna! Bumbu meresap dengan baik. Terima kasih resepnya!',
        date: '1 minggu yang lalu',
      },
    ],
  };
};

export default function App() {
  const [currentPage, setCurrentPage] = useState<'home' | 'detail' | 'add' | 'edit' | 'profile' | 'about' | 'contact' | 'login' | 'register' | 'admin'>('home');
  const [selectedRecipeId, setSelectedRecipeId] = useState<string | null>(null);
  const [editingRecipeId, setEditingRecipeId] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [user, setUser] = useState<User | null>(null);
  const [recipes, setRecipes] = useState<Recipe[]>(MOCK_RECIPES);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [recipeToDelete, setRecipeToDelete] = useState<Recipe | null>(null);
  const [likedRecipeIds, setLikedRecipeIds] = useState<string[]>([]);
  const [filters, setFilters] = useState<Filters>({
    categories: [],
    regions: [],
    cookingTime: 'all',
    minRating: 0,
  });

  // Filter recipes based on search and filters
  const filteredRecipes = useMemo(() => {
    let result = recipes;

    // Search filter
    if (searchQuery) {
      result = result.filter(recipe =>
        recipe.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        recipe.description.toLowerCase().includes(searchQuery.toLowerCase())
      );
    }

    // Category filter
    if (filters.categories.length > 0) {
      result = result.filter(recipe =>
        filters.categories.includes(recipe.category)
      );
    }

    // Region filter
    if (filters.regions.length > 0) {
      result = result.filter(recipe =>
        filters.regions.includes(recipe.region)
      );
    }

    // Cooking time filter
    if (filters.cookingTime !== 'all') {
      result = result.filter(recipe => {
        const time = parseInt(recipe.cookingTime);
        switch (filters.cookingTime) {
          case '0-15':
            return time < 15;
          case '15-30':
            return time >= 15 && time <= 30;
          case '30-60':
            return time > 30 && time <= 60;
          case '60+':
            return time > 60;
          default:
            return true;
        }
      });
    }

    // Rating filter
    if (filters.minRating > 0) {
      result = result.filter(recipe => recipe.rating >= filters.minRating);
    }

    return result;
  }, [searchQuery, filters, recipes]);

  // Mock data untuk user profile
  const myRecipes = user ? recipes.filter(r => r.author === user.name) : [];
  const likedRecipes = recipes.filter(r => likedRecipeIds.includes(r.id));

  const handleRecipeClick = (id: string) => {
    setSelectedRecipeId(id);
    setCurrentPage('detail');
  };

  const handleNavigate = (page: string) => {
    // Require login for certain pages
    if (!user && (page === 'add' || page === 'profile')) {
      toast.error('Silakan login terlebih dahulu');
      setCurrentPage('login');
      return;
    }
    
    // Require admin for admin dashboard
    if (page === 'admin' && user?.role !== 'admin') {
      toast.error('Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
      return;
    }
    
    setCurrentPage(page as any);
  };

  const handleAddRecipe = (recipeData: any) => {
    const newRecipe: Recipe = {
      id: Date.now().toString(),
      title: recipeData.title,
      image: recipeData.image,
      author: user?.name || 'Anonymous',
      authorAvatar: `https://api.dicebear.com/7.x/avataaars/svg?seed=${user?.name}`,
      cookingTime: recipeData.cookingTime,
      category: recipeData.category,
      region: recipeData.region,
      rating: 0,
      reviewCount: 0,
      description: recipeData.description,
    };
    
    setRecipes([newRecipe, ...recipes]);
    toast.success('Resep berhasil dipublikasikan!');
    setCurrentPage(user?.role === 'admin' ? 'admin' : 'home');
  };

  const handleEditRecipe = (recipeData: any) => {
    setRecipes(recipes.map(r => 
      r.id === recipeData.id 
        ? { ...r, ...recipeData }
        : r
    ));
    toast.success('Resep berhasil diperbarui!');
    setEditingRecipeId(null);
    setCurrentPage(user?.role === 'admin' ? 'admin' : 'detail');
  };

  const handleDeleteRecipe = (id: string) => {
    const recipe = recipes.find(r => r.id === id);
    if (!recipe) return;
    
    setRecipeToDelete(recipe);
    setDeleteDialogOpen(true);
  };

  const confirmDeleteRecipe = () => {
    if (!recipeToDelete) return;
    
    setRecipes(recipes.filter(r => r.id !== recipeToDelete.id));
    toast.success(`Resep "${recipeToDelete.title}" berhasil dihapus`);
    setDeleteDialogOpen(false);
    setRecipeToDelete(null);
    
    // Return to home or admin dashboard
    if (currentPage === 'detail') {
      setCurrentPage(user?.role === 'admin' ? 'admin' : 'home');
      setSelectedRecipeId(null);
    }
  };

  const handleLogin = (email: string, name: string) => {
    // Mock: If email contains 'admin', set as admin
    const role = email.toLowerCase().includes('admin') ? 'admin' : 'user';
    setUser({ email, name, role });
    setCurrentPage('home');
    
    if (role === 'admin') {
      toast.success(`Selamat datang Admin ${name}!`);
    }
  };

  const handleRegister = (email: string, name: string) => {
    const role = email.toLowerCase().includes('admin') ? 'admin' : 'user';
    setUser({ email, name, role });
    setCurrentPage('home');
  };

  const handleLogout = () => {
    setUser(null);
    setCurrentPage('home');
    toast.success('Anda telah keluar');
  };

  const handleToggleLike = (recipeId: string) => {
    if (!user) {
      toast.error('Silakan login untuk menyukai resep');
      setCurrentPage('login');
      return;
    }

    setLikedRecipeIds(prev => {
      if (prev.includes(recipeId)) {
        toast.success('Resep dihapus dari favorit');
        return prev.filter(id => id !== recipeId);
      } else {
        toast.success('Resep ditambahkan ke favorit');
        return [...prev, recipeId];
      }
    });
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {currentPage !== 'login' && currentPage !== 'register' && (
        <Header
          onNavigate={handleNavigate}
          currentPage={currentPage}
          searchQuery={searchQuery}
          onSearchChange={setSearchQuery}
          user={user}
          onLogout={handleLogout}
        />
      )}

      {currentPage === 'home' && (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div className="flex gap-8">
            {/* Desktop Filter Sidebar */}
            <aside className="hidden lg:block w-64 flex-shrink-0">
              <div className="sticky top-24">
                <FilterSidebar
                  filters={filters}
                  onFiltersChange={setFilters}
                />
              </div>
            </aside>

            {/* Main Content */}
            <main className="flex-1 min-w-0">
              {/* Mobile Filter Button */}
              <div className="lg:hidden mb-6">
                <Sheet>
                  <SheetTrigger asChild>
                    <Button variant="outline" className="w-full">
                      <SlidersHorizontal className="w-4 h-4 mr-2" />
                      Filter & Kategori
                    </Button>
                  </SheetTrigger>
                  <SheetContent side="left" className="w-full sm:w-96 p-6">
                    <FilterSidebar
                      filters={filters}
                      onFiltersChange={setFilters}
                      isMobile
                    />
                  </SheetContent>
                </Sheet>
              </div>

              {/* Results Header */}
              <div className="mb-6">
                <h1 className="mb-2">Resep Nusantara</h1>
                <p className="text-gray-600">
                  Menampilkan {filteredRecipes.length} resep
                </p>
              </div>

              {/* Recipe Grid */}
              <RecipeList
                recipes={filteredRecipes}
                onRecipeClick={handleRecipeClick}
                isAdmin={user?.role === 'admin'}
                onEdit={(id) => {
                  setEditingRecipeId(id);
                  setCurrentPage('edit');
                }}
                onDelete={handleDeleteRecipe}
                likedRecipeIds={likedRecipeIds}
                onToggleLike={handleToggleLike}
              />
            </main>
          </div>
        </div>
      )}

      {currentPage === 'detail' && selectedRecipeId && (
        <RecipeDetail
          recipe={getRecipeDetail(selectedRecipeId)!}
          onBack={() => setCurrentPage(user?.role === 'admin' ? 'admin' : 'home')}
          isAdmin={user?.role === 'admin'}
          onEdit={() => {
            setEditingRecipeId(selectedRecipeId);
            setCurrentPage('edit');
          }}
          onDelete={() => handleDeleteRecipe(selectedRecipeId)}
          isLiked={likedRecipeIds.includes(selectedRecipeId)}
          onToggleLike={() => handleToggleLike(selectedRecipeId)}
        />
      )}

      {currentPage === 'add' && (
        <AddRecipeForm
          onBack={() => setCurrentPage(user?.role === 'admin' ? 'admin' : 'home')}
          onSubmit={handleAddRecipe}
        />
      )}

      {currentPage === 'edit' && editingRecipeId && (
        <EditRecipeForm
          recipe={recipes.find(r => r.id === editingRecipeId)!}
          onBack={() => {
            setEditingRecipeId(null);
            setCurrentPage(user?.role === 'admin' ? 'admin' : 'detail');
          }}
          onSubmit={handleEditRecipe}
        />
      )}

      {currentPage === 'admin' && user?.role === 'admin' && (
        <AdminDashboard
          recipes={recipes}
          onEdit={(id) => {
            setEditingRecipeId(id);
            setCurrentPage('edit');
          }}
          onDelete={handleDeleteRecipe}
          onView={(id) => {
            setSelectedRecipeId(id);
            setCurrentPage('detail');
          }}
          onAddNew={() => setCurrentPage('add')}
        />
      )}

      {currentPage === 'profile' && user && (
        <UserProfile
          onBack={() => setCurrentPage('home')}
          onRecipeClick={handleRecipeClick}
          myRecipes={myRecipes}
          likedRecipes={likedRecipes}
          user={user}
          onToggleLike={handleToggleLike}
        />
      )}

      {currentPage === 'about' && (
        <AboutUs onBack={() => setCurrentPage('home')} />
      )}

      {currentPage === 'contact' && (
        <ContactUs onBack={() => setCurrentPage('home')} />
      )}

      {currentPage === 'login' && (
        <Login
          onLogin={handleLogin}
          onSwitchToRegister={() => setCurrentPage('register')}
          onBack={() => setCurrentPage('home')}
        />
      )}

      {currentPage === 'register' && (
        <Register
          onRegister={handleRegister}
          onSwitchToLogin={() => setCurrentPage('login')}
          onBack={() => setCurrentPage('home')}
        />
      )}

      {/* Delete Confirmation Dialog */}
      <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Hapus Resep?</AlertDialogTitle>
            <AlertDialogDescription>
              Apakah Anda yakin ingin menghapus resep <span className="font-semibold">"{recipeToDelete?.title}"</span>?
              <br />
              <br />
              Tindakan ini tidak dapat dibatalkan dan resep akan dihapus secara permanen.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Batal</AlertDialogCancel>
            <AlertDialogAction
              onClick={confirmDeleteRecipe}
              className="bg-red-600 hover:bg-red-700"
            >
              Hapus
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <Toaster />
    </div>
  );
}
