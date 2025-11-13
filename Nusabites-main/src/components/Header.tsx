import { ChefHat, Search, User, Plus, Menu, LogOut, LogIn, Shield } from 'lucide-react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Sheet, SheetContent, SheetTrigger } from './ui/sheet';
import { Avatar, AvatarFallback } from './ui/avatar';
import { Badge } from './ui/badge';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from './ui/dropdown-menu';
import { useState } from 'react';

interface User {
  name: string;
  email: string;
  role: 'user' | 'admin';
}

interface HeaderProps {
  onNavigate: (page: string) => void;
  currentPage: string;
  searchQuery: string;
  onSearchChange: (query: string) => void;
  user: User | null;
  onLogout: () => void;
}

export function Header({ onNavigate, currentPage, searchQuery, onSearchChange, user, onLogout }: HeaderProps) {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const handleNavigate = (page: string) => {
    onNavigate(page);
    setIsMobileMenuOpen(false);
  };

  const handleLogout = () => {
    onLogout();
    setIsMobileMenuOpen(false);
  };

  return (
    <header className="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <button 
            onClick={() => onNavigate('home')}
            className="flex items-center gap-2 hover:opacity-80 transition-opacity"
          >
            <div className="bg-gradient-to-br from-orange-500 to-red-600 p-2 rounded-lg">
              <ChefHat className="w-6 h-6 text-white" />
            </div>
            <span className="text-orange-600">Nusa Bites</span>
          </button>

          {/* Desktop Navigation Links */}
          <nav className="hidden lg:flex items-center gap-1">
            <Button
              variant="ghost"
              onClick={() => onNavigate('home')}
              className={currentPage === 'home' ? 'text-orange-600' : ''}
            >
              Beranda
            </Button>
            <Button
              variant="ghost"
              onClick={() => onNavigate('about')}
              className={currentPage === 'about' ? 'text-orange-600' : ''}
            >
              Tentang Kami
            </Button>
            <Button
              variant="ghost"
              onClick={() => onNavigate('contact')}
              className={currentPage === 'contact' ? 'text-orange-600' : ''}
            >
              Kontak
            </Button>
          </nav>

          {/* Search Bar - Hidden on mobile */}
          <div className="hidden md:flex flex-1 max-w-md mx-4">
            <div className="relative w-full">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <Input
                type="text"
                placeholder="Cari resep..."
                value={searchQuery}
                onChange={(e) => onSearchChange(e.target.value)}
                className="pl-10 w-full"
              />
            </div>
          </div>

          {/* Desktop Action Buttons */}
          <div className="hidden lg:flex items-center gap-2">
            {user ? (
              <>
                {user.role === 'admin' && (
                  <Button
                    variant={currentPage === 'admin' ? 'default' : 'ghost'}
                    onClick={() => onNavigate('admin')}
                    className={currentPage === 'admin' ? 'bg-orange-600 hover:bg-orange-700' : ''}
                  >
                    <Shield className="w-4 h-4 mr-2" />
                    Dashboard Admin
                  </Button>
                )}
                
                <Button
                  variant={currentPage === 'add' ? 'default' : 'ghost'}
                  onClick={() => onNavigate('add')}
                  className={currentPage === 'add' ? 'bg-orange-600 hover:bg-orange-700' : ''}
                >
                  <Plus className="w-4 h-4 mr-2" />
                  Tambah Resep
                </Button>
                
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="gap-2">
                      <Avatar className="w-8 h-8">
                        <AvatarFallback className={user.role === 'admin' ? 'bg-orange-600 text-white' : 'bg-orange-100 text-orange-700'}>
                          {user.name.charAt(0).toUpperCase()}
                        </AvatarFallback>
                      </Avatar>
                      <span className="hidden xl:inline">
                        {user.name}
                        {user.role === 'admin' && <span className="text-orange-600 ml-1">(Admin)</span>}
                      </span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>
                      <div className="flex flex-col">
                        <div className="flex items-center gap-2">
                          <span>{user.name}</span>
                          {user.role === 'admin' && (
                            <Badge variant="outline" className="border-orange-200 text-orange-700">
                              Admin
                            </Badge>
                          )}
                        </div>
                        <span className="text-gray-500">{user.email}</span>
                      </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    {user.role === 'admin' && (
                      <>
                        <DropdownMenuItem onClick={() => onNavigate('admin')}>
                          <Shield className="w-4 h-4 mr-2" />
                          Dashboard Admin
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                      </>
                    )}
                    <DropdownMenuItem onClick={() => onNavigate('profile')}>
                      <User className="w-4 h-4 mr-2" />
                      Profil Saya
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem onClick={onLogout} className="text-red-600">
                      <LogOut className="w-4 h-4 mr-2" />
                      Keluar
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </>
            ) : (
              <>
                <Button
                  variant="ghost"
                  onClick={() => onNavigate('login')}
                >
                  <LogIn className="w-4 h-4 mr-2" />
                  Masuk
                </Button>
                <Button
                  onClick={() => onNavigate('register')}
                  className="bg-orange-600 hover:bg-orange-700"
                >
                  Daftar
                </Button>
              </>
            )}
          </div>

          {/* Mobile Menu Button */}
          <Sheet open={isMobileMenuOpen} onOpenChange={setIsMobileMenuOpen}>
            <SheetTrigger asChild className="lg:hidden">
              <Button variant="ghost" size="icon">
                <Menu className="w-5 h-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="right" className="w-full sm:w-80">
              <nav className="flex flex-col gap-4 mt-8">
                {user && (
                  <div className="mb-4 p-4 bg-orange-50 rounded-lg">
                    <div className="flex items-center gap-3">
                      <Avatar className="w-12 h-12">
                        <AvatarFallback className={user.role === 'admin' ? 'bg-orange-600 text-white' : 'bg-orange-100 text-orange-700'}>
                          {user.name.charAt(0).toUpperCase()}
                        </AvatarFallback>
                      </Avatar>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2">
                          <p className="truncate">{user.name}</p>
                          {user.role === 'admin' && (
                            <Badge variant="outline" className="border-orange-200 text-orange-700">
                              Admin
                            </Badge>
                          )}
                        </div>
                        <p className="text-gray-600 text-sm truncate">{user.email}</p>
                      </div>
                    </div>
                  </div>
                )}
                
                <Button
                  variant="ghost"
                  onClick={() => handleNavigate('home')}
                  className={`justify-start ${currentPage === 'home' ? 'text-orange-600 bg-orange-50' : ''}`}
                >
                  Beranda
                </Button>
                <Button
                  variant="ghost"
                  onClick={() => handleNavigate('about')}
                  className={`justify-start ${currentPage === 'about' ? 'text-orange-600 bg-orange-50' : ''}`}
                >
                  Tentang Kami
                </Button>
                <Button
                  variant="ghost"
                  onClick={() => handleNavigate('contact')}
                  className={`justify-start ${currentPage === 'contact' ? 'text-orange-600 bg-orange-50' : ''}`}
                >
                  Kontak
                </Button>
                
                {user ? (
                  <>
                    <div className="border-t my-2" />
                    <Button
                      variant={currentPage === 'add' ? 'default' : 'ghost'}
                      onClick={() => handleNavigate('add')}
                      className={`justify-start ${currentPage === 'add' ? 'bg-orange-600 hover:bg-orange-700' : ''}`}
                    >
                      <Plus className="w-4 h-4 mr-2" />
                      Tambah Resep
                    </Button>
                    <Button
                      variant={currentPage === 'profile' ? 'default' : 'ghost'}
                      onClick={() => handleNavigate('profile')}
                      className={`justify-start ${currentPage === 'profile' ? 'bg-orange-600 hover:bg-orange-700' : ''}`}
                    >
                      <User className="w-4 h-4 mr-2" />
                      Profil
                    </Button>
                    <div className="border-t my-2" />
                    <Button
                      variant="ghost"
                      onClick={handleLogout}
                      className="justify-start text-red-600 hover:text-red-700 hover:bg-red-50"
                    >
                      <LogOut className="w-4 h-4 mr-2" />
                      Keluar
                    </Button>
                  </>
                ) : (
                  <>
                    <div className="border-t my-2" />
                    <Button
                      variant="ghost"
                      onClick={() => handleNavigate('login')}
                      className="justify-start"
                    >
                      <LogIn className="w-4 h-4 mr-2" />
                      Masuk
                    </Button>
                    <Button
                      onClick={() => handleNavigate('register')}
                      className="justify-start bg-orange-600 hover:bg-orange-700"
                    >
                      Daftar Sekarang
                    </Button>
                  </>
                )}
              </nav>
            </SheetContent>
          </Sheet>
        </div>

        {/* Mobile Search Bar */}
        <div className="md:hidden pb-4">
          <div className="relative w-full">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
            <Input
              type="text"
              placeholder="Cari resep..."
              value={searchQuery}
              onChange={(e) => onSearchChange(e.target.value)}
              className="pl-10 w-full"
            />
          </div>
        </div>
      </div>
    </header>
  );
}
