import { useState } from 'react';
import { ArrowLeft, Edit, Trash2, Heart, Settings } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import { Avatar, AvatarFallback, AvatarImage } from './ui/avatar';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './ui/tabs';
import { RecipeCard, Recipe } from './RecipeCard';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';

interface User {
  name: string;
  email: string;
  role: 'user' | 'admin';
}

interface UserProfileProps {
  onBack: () => void;
  onRecipeClick: (id: string) => void;
  myRecipes: Recipe[];
  likedRecipes: Recipe[];
  user: User;
  onToggleLike?: (id: string) => void;
}

export function UserProfile({ onBack, onRecipeClick, myRecipes, likedRecipes, user, onToggleLike }: UserProfileProps) {
  const [isEditProfileOpen, setIsEditProfileOpen] = useState(false);
  const [name, setName] = useState(user.name);
  const [email, setEmail] = useState(user.email);
  const [bio, setBio] = useState('Pecinta kuliner nusantara yang suka berbagi resep keluarga.');

  const handleSaveProfile = () => {
    // This would save to backend
    console.log('Save profile:', { name, email, bio });
    setIsEditProfileOpen(false);
  };

  const handleDeleteRecipe = (recipeId: string) => {
    // This would delete from backend
    console.log('Delete recipe:', recipeId);
    // Show confirmation dialog in real app
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <Button 
        variant="ghost" 
        onClick={onBack}
        className="mb-6"
      >
        <ArrowLeft className="w-4 h-4 mr-2" />
        Kembali
      </Button>

      {/* Profile Header */}
      <Card className="mb-8">
        <CardContent className="p-8">
          <div className="flex flex-col md:flex-row gap-8 items-start">
            <Avatar className="w-32 h-32">
              <AvatarImage src={`https://api.dicebear.com/7.x/avataaars/svg?seed=${user.name}`} />
              <AvatarFallback>{user.name.split(' ').map(n => n[0]).join('').toUpperCase()}</AvatarFallback>
            </Avatar>

            <div className="flex-1">
              <div className="flex items-start justify-between mb-4">
                <div>
                  <h1 className="mb-2">{name}</h1>
                  <p className="text-gray-600">{email}</p>
                </div>
                
                <Dialog open={isEditProfileOpen} onOpenChange={setIsEditProfileOpen}>
                  <DialogTrigger asChild>
                    <Button variant="outline">
                      <Settings className="w-4 h-4 mr-2" />
                      Edit Profil
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Edit Profil</DialogTitle>
                      <DialogDescription>
                        Ubah informasi profil Anda di bawah ini.
                      </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4 py-4">
                      <div>
                        <Label htmlFor="name">Nama Lengkap</Label>
                        <Input
                          id="name"
                          value={name}
                          onChange={(e) => setName(e.target.value)}
                          className="mt-2"
                        />
                      </div>
                      <div>
                        <Label htmlFor="email">Email</Label>
                        <Input
                          id="email"
                          type="email"
                          value={email}
                          onChange={(e) => setEmail(e.target.value)}
                          className="mt-2"
                        />
                      </div>
                      <div>
                        <Label htmlFor="bio">Bio</Label>
                        <Input
                          id="bio"
                          value={bio}
                          onChange={(e) => setBio(e.target.value)}
                          className="mt-2"
                        />
                      </div>
                      <div>
                        <Label htmlFor="password">Password Baru</Label>
                        <Input
                          id="password"
                          type="password"
                          placeholder="Kosongkan jika tidak ingin mengubah"
                          className="mt-2"
                        />
                      </div>
                      <div>
                        <Label htmlFor="avatar">URL Foto Profil</Label>
                        <Input
                          id="avatar"
                          placeholder="https://example.com/avatar.jpg"
                          className="mt-2"
                        />
                      </div>
                      <div className="flex gap-3 justify-end pt-4">
                        <Button variant="outline" onClick={() => setIsEditProfileOpen(false)}>
                          Batal
                        </Button>
                        <Button 
                          onClick={handleSaveProfile}
                          className="bg-orange-600 hover:bg-orange-700"
                        >
                          Simpan
                        </Button>
                      </div>
                    </div>
                  </DialogContent>
                </Dialog>
              </div>

              <p className="text-gray-600 mb-6">{bio}</p>

              <div className="flex gap-8">
                <div>
                  <div className="text-orange-600">{myRecipes.length}</div>
                  <div className="text-sm text-gray-600">Resep</div>
                </div>
                <div>
                  <div className="text-orange-600">{likedRecipes.length}</div>
                  <div className="text-sm text-gray-600">Disukai</div>
                </div>
                <div>
                  <div className="text-orange-600">
                    {myRecipes.reduce((sum, r) => sum + r.reviewCount, 0)}
                  </div>
                  <div className="text-sm text-gray-600">Review</div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Tabs */}
      <Tabs defaultValue="my-recipes" className="w-full">
        <TabsList className="grid w-full max-w-md grid-cols-2 mb-8">
          <TabsTrigger value="my-recipes">Resep Saya</TabsTrigger>
          <TabsTrigger value="liked">Resep Disukai</TabsTrigger>
        </TabsList>

        <TabsContent value="my-recipes">
          {myRecipes.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {myRecipes.map((recipe) => (
                <div key={recipe.id} className="relative group">
                  <RecipeCard
                    recipe={recipe}
                    onClick={() => onRecipeClick(recipe.id)}
                  />
                  <div className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                    <Button
                      size="icon"
                      variant="secondary"
                      className="bg-white/90 hover:bg-white"
                      onClick={(e) => {
                        e.stopPropagation();
                        // Edit recipe
                      }}
                    >
                      <Edit className="w-4 h-4" />
                    </Button>
                    <Button
                      size="icon"
                      variant="secondary"
                      className="bg-white/90 hover:bg-white text-red-600"
                      onClick={(e) => {
                        e.stopPropagation();
                        handleDeleteRecipe(recipe.id);
                      }}
                    >
                      <Trash2 className="w-4 h-4" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <Card>
              <CardContent className="p-12 text-center">
                <p className="text-gray-500 mb-4">Anda belum memiliki resep.</p>
                <Button 
                  onClick={onBack}
                  className="bg-orange-600 hover:bg-orange-700"
                >
                  Tambah Resep Pertama
                </Button>
              </CardContent>
            </Card>
          )}
        </TabsContent>

        <TabsContent value="liked">
          {likedRecipes.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {likedRecipes.map((recipe) => (
                <div key={recipe.id} className="relative group">
                  <RecipeCard
                    recipe={recipe}
                    onClick={() => onRecipeClick(recipe.id)}
                  />
                  {onToggleLike && (
                    <Button
                      size="icon"
                      variant="secondary"
                      className="absolute top-2 right-2 bg-white/90 hover:bg-white opacity-0 group-hover:opacity-100 transition-opacity"
                      onClick={(e) => {
                        e.stopPropagation();
                        onToggleLike(recipe.id);
                      }}
                    >
                      <Heart className="w-4 h-4 fill-red-500 text-red-500" />
                    </Button>
                  )}
                </div>
              ))}
            </div>
          ) : (
            <Card>
              <CardContent className="p-12 text-center">
                <p className="text-gray-500">Anda belum menyukai resep apapun.</p>
              </CardContent>
            </Card>
          )}
        </TabsContent>
      </Tabs>
    </div>
  );
}
