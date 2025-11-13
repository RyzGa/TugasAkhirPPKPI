import { useState } from 'react';
import { ArrowLeft, Plus, X, Upload } from 'lucide-react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Textarea } from './ui/textarea';
import { Label } from './ui/label';
import { Card, CardContent } from './ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './ui/select';
import { Recipe } from './RecipeCard';

interface EditRecipeFormProps {
  recipe: Recipe;
  onBack: () => void;
  onSubmit: (recipeData: any) => void;
}

export function EditRecipeForm({ recipe, onBack, onSubmit }: EditRecipeFormProps) {
  const [title, setTitle] = useState(recipe.title);
  const [category, setCategory] = useState(recipe.category);
  const [region, setRegion] = useState(recipe.region);
  const [cookingTime, setCookingTime] = useState(recipe.cookingTime);
  const [description, setDescription] = useState(recipe.description);
  const [imageUrl, setImageUrl] = useState(recipe.image);
  const [ingredients, setIngredients] = useState<string[]>(['Bahan 1', 'Bahan 2', 'Bahan 3']);
  const [steps, setSteps] = useState<string[]>(['Langkah 1', 'Langkah 2', 'Langkah 3']);

  const addIngredient = () => {
    setIngredients([...ingredients, '']);
  };

  const removeIngredient = (index: number) => {
    setIngredients(ingredients.filter((_, i) => i !== index));
  };

  const updateIngredient = (index: number, value: string) => {
    const newIngredients = [...ingredients];
    newIngredients[index] = value;
    setIngredients(newIngredients);
  };

  const addStep = () => {
    setSteps([...steps, '']);
  };

  const removeStep = (index: number) => {
    setSteps(steps.filter((_, i) => i !== index));
  };

  const updateStep = (index: number, value: string) => {
    const newSteps = [...steps];
    newSteps[index] = value;
    setSteps(newSteps);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const recipeData = {
      id: recipe.id,
      title,
      category,
      region,
      cookingTime,
      description,
      image: imageUrl,
      ingredients: ingredients.filter(i => i.trim() !== ''),
      steps: steps.filter(s => s.trim() !== ''),
      author: recipe.author,
      authorAvatar: recipe.authorAvatar,
      rating: recipe.rating,
      reviewCount: recipe.reviewCount,
    };

    onSubmit(recipeData);
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="flex items-center gap-4 mb-8">
          <Button variant="ghost" onClick={onBack}>
            <ArrowLeft className="w-5 h-5" />
          </Button>
          <div>
            <h1 className="mb-0">Edit Resep</h1>
            <p className="text-gray-600">Perbarui informasi resep</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Basic Information */}
          <Card>
            <CardContent className="p-6">
              <h2 className="mb-6">Informasi Dasar</h2>

              <div className="space-y-4">
                <div>
                  <Label htmlFor="title">Judul Resep *</Label>
                  <Input
                    id="title"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    placeholder="Contoh: Nasi Goreng Spesial"
                    required
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="category">Kategori *</Label>
                    <Select value={category} onValueChange={setCategory} required>
                      <SelectTrigger>
                        <SelectValue placeholder="Pilih kategori" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Makanan Utama">Makanan Utama</SelectItem>
                        <SelectItem value="Camilan">Camilan</SelectItem>
                        <SelectItem value="Minuman">Minuman</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label htmlFor="region">Asal Daerah *</Label>
                    <Select value={region} onValueChange={setRegion} required>
                      <SelectTrigger>
                        <SelectValue placeholder="Pilih daerah" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Jawa">Jawa</SelectItem>
                        <SelectItem value="Sumatera">Sumatera</SelectItem>
                        <SelectItem value="Bali">Bali</SelectItem>
                        <SelectItem value="Kalimantan">Kalimantan</SelectItem>
                        <SelectItem value="Sulawesi">Sulawesi</SelectItem>
                        <SelectItem value="Papua">Papua</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div>
                  <Label htmlFor="cookingTime">Waktu Memasak *</Label>
                  <Input
                    id="cookingTime"
                    value={cookingTime}
                    onChange={(e) => setCookingTime(e.target.value)}
                    placeholder="Contoh: 30 menit"
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="description">Deskripsi *</Label>
                  <Textarea
                    id="description"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    placeholder="Ceritakan tentang resep Anda..."
                    rows={4}
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="image">URL Gambar *</Label>
                  <div className="flex gap-2">
                    <Input
                      id="image"
                      value={imageUrl}
                      onChange={(e) => setImageUrl(e.target.value)}
                      placeholder="https://example.com/image.jpg"
                      required
                    />
                    <Button type="button" variant="outline">
                      <Upload className="w-4 h-4" />
                    </Button>
                  </div>
                  {imageUrl && (
                    <div className="mt-4 w-full h-48 rounded-lg overflow-hidden bg-gray-100">
                      <img
                        src={imageUrl}
                        alt="Preview"
                        className="w-full h-full object-cover"
                        onError={(e) => {
                          e.currentTarget.src = 'https://via.placeholder.com/400x300?text=Preview';
                        }}
                      />
                    </div>
                  )}
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Ingredients */}
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="mb-0">Bahan-bahan</h2>
                <Button type="button" onClick={addIngredient} variant="outline" size="sm">
                  <Plus className="w-4 h-4 mr-2" />
                  Tambah Bahan
                </Button>
              </div>

              <div className="space-y-3">
                {ingredients.map((ingredient, index) => (
                  <div key={index} className="flex gap-2">
                    <Input
                      value={ingredient}
                      onChange={(e) => updateIngredient(index, e.target.value)}
                      placeholder={`Bahan ${index + 1}`}
                    />
                    {ingredients.length > 1 && (
                      <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={() => removeIngredient(index)}
                      >
                        <X className="w-4 h-4" />
                      </Button>
                    )}
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Steps */}
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="mb-0">Langkah Memasak</h2>
                <Button type="button" onClick={addStep} variant="outline" size="sm">
                  <Plus className="w-4 h-4 mr-2" />
                  Tambah Langkah
                </Button>
              </div>

              <div className="space-y-3">
                {steps.map((step, index) => (
                  <div key={index} className="flex gap-2">
                    <div className="flex-shrink-0 w-8 h-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mt-2">
                      {index + 1}
                    </div>
                    <Textarea
                      value={step}
                      onChange={(e) => updateStep(index, e.target.value)}
                      placeholder={`Langkah ${index + 1}`}
                      rows={2}
                      className="flex-1"
                    />
                    {steps.length > 1 && (
                      <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={() => removeStep(index)}
                        className="mt-2"
                      >
                        <X className="w-4 h-4" />
                      </Button>
                    )}
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Submit Buttons */}
          <div className="flex gap-4 justify-end">
            <Button type="button" variant="outline" onClick={onBack}>
              Batal
            </Button>
            <Button type="submit" className="bg-orange-600 hover:bg-orange-700">
              Simpan Perubahan
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}
