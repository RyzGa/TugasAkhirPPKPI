import { useState } from 'react';
import { ArrowLeft, Plus, X, Upload } from 'lucide-react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Textarea } from './ui/textarea';
import { Label } from './ui/label';
import { Card, CardContent } from './ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './ui/select';

interface AddRecipeFormProps {
  onBack: () => void;
  onSubmit: (recipe: any) => void;
}

export function AddRecipeForm({ onBack, onSubmit }: AddRecipeFormProps) {
  const [title, setTitle] = useState('');
  const [category, setCategory] = useState('');
  const [region, setRegion] = useState('');
  const [cookingTime, setCookingTime] = useState('');
  const [description, setDescription] = useState('');
  const [imageUrl, setImageUrl] = useState('');
  const [ingredients, setIngredients] = useState(['']);
  const [steps, setSteps] = useState(['']);

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
      title,
      category,
      region,
      cookingTime,
      description,
      image: imageUrl,
      ingredients: ingredients.filter(i => i.trim() !== ''),
      steps: steps.filter(s => s.trim() !== ''),
    };

    onSubmit(recipeData);
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <Button 
        variant="ghost" 
        onClick={onBack}
        className="mb-6"
      >
        <ArrowLeft className="w-4 h-4 mr-2" />
        Kembali
      </Button>

      <h1 className="mb-8">Tambah Resep Baru</h1>

      <form onSubmit={handleSubmit}>
        <Card className="mb-6">
          <CardContent className="p-6">
            <h2 className="mb-6">Informasi Dasar</h2>

            <div className="space-y-6">
              {/* Title */}
              <div>
                <Label htmlFor="title">Judul Resep *</Label>
                <Input
                  id="title"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  placeholder="Contoh: Nasi Goreng Spesial"
                  required
                  className="mt-2"
                />
              </div>

              {/* Description */}
              <div>
                <Label htmlFor="description">Deskripsi</Label>
                <Textarea
                  id="description"
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder="Ceritakan tentang resep ini..."
                  rows={4}
                  className="mt-2"
                />
              </div>

              {/* Image URL */}
              <div>
                <Label htmlFor="image">URL Gambar</Label>
                <div className="mt-2">
                  <Input
                    id="image"
                    value={imageUrl}
                    onChange={(e) => setImageUrl(e.target.value)}
                    placeholder="https://example.com/image.jpg"
                  />
                  <p className="text-sm text-gray-500 mt-2">
                    <Upload className="w-4 h-4 inline mr-1" />
                    Masukkan URL gambar resep Anda
                  </p>
                </div>
              </div>

              <div className="grid md:grid-cols-2 gap-6">
                {/* Category */}
                <div>
                  <Label htmlFor="category">Kategori *</Label>
                  <Select value={category} onValueChange={setCategory} required>
                    <SelectTrigger className="mt-2">
                      <SelectValue placeholder="Pilih kategori" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Makanan Utama">Makanan Utama</SelectItem>
                      <SelectItem value="Camilan">Camilan</SelectItem>
                      <SelectItem value="Minuman">Minuman</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                {/* Region */}
                <div>
                  <Label htmlFor="region">Asal Daerah *</Label>
                  <Select value={region} onValueChange={setRegion} required>
                    <SelectTrigger className="mt-2">
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

              {/* Cooking Time */}
              <div>
                <Label htmlFor="cookingTime">Waktu Memasak *</Label>
                <Input
                  id="cookingTime"
                  value={cookingTime}
                  onChange={(e) => setCookingTime(e.target.value)}
                  placeholder="Contoh: 30 menit"
                  required
                  className="mt-2"
                />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Ingredients */}
        <Card className="mb-6">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-6">
              <h2>Bahan-Bahan</h2>
              <Button type="button" onClick={addIngredient} variant="outline">
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
        <Card className="mb-6">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-6">
              <h2>Langkah-Langkah</h2>
              <Button type="button" onClick={addStep} variant="outline">
                <Plus className="w-4 h-4 mr-2" />
                Tambah Langkah
              </Button>
            </div>

            <div className="space-y-4">
              {steps.map((step, index) => (
                <div key={index} className="flex gap-2">
                  <div className="flex-shrink-0 w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center mt-2">
                    {index + 1}
                  </div>
                  <Textarea
                    value={step}
                    onChange={(e) => updateStep(index, e.target.value)}
                    placeholder={`Langkah ${index + 1}`}
                    rows={3}
                    className="flex-1"
                  />
                  {steps.length > 1 && (
                    <Button
                      type="button"
                      variant="ghost"
                      size="icon"
                      onClick={() => removeStep(index)}
                      className="flex-shrink-0"
                    >
                      <X className="w-4 h-4" />
                    </Button>
                  )}
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Submit */}
        <div className="flex gap-4 justify-end">
          <Button type="button" variant="outline" onClick={onBack}>
            Batal
          </Button>
          <Button 
            type="submit" 
            className="bg-orange-600 hover:bg-orange-700"
          >
            Publikasikan Resep
          </Button>
        </div>
      </form>
    </div>
  );
}
