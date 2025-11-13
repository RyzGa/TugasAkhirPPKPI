import { Clock, Star, User, ChefHat, ArrowLeft, Heart } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import { Badge } from './ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from './ui/avatar';
import { Textarea } from './ui/textarea';
import { Separator } from './ui/separator';
import { ImageWithFallback } from './figma/ImageWithFallback';
import { useState } from 'react';

export interface RecipeDetailData {
  id: string;
  title: string;
  image: string;
  author: string;
  authorAvatar: string;
  cookingTime: string;
  category: string;
  region: string;
  rating: number;
  reviewCount: number;
  description: string;
  ingredients: string[];
  steps: string[];
  reviews: Array<{
    id: string;
    author: string;
    avatar: string;
    rating: number;
    comment: string;
    date: string;
  }>;
}

interface RecipeDetailProps {
  recipe: RecipeDetailData;
  onBack: () => void;
  isAdmin?: boolean;
  onEdit?: () => void;
  onDelete?: () => void;
  isLiked?: boolean;
  onToggleLike?: () => void;
}

export function RecipeDetail({ recipe, onBack, isAdmin = false, onEdit, onDelete, isLiked = false, onToggleLike }: RecipeDetailProps) {
  const [newReview, setNewReview] = useState('');
  const [newRating, setNewRating] = useState(5);

  const handleSubmitReview = () => {
    // This would submit to backend
    console.log('Submit review:', { rating: newRating, comment: newReview });
    setNewReview('');
    setNewRating(5);
  };

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex items-center justify-between mb-6">
        <Button 
          variant="ghost" 
          onClick={onBack}
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Kembali
        </Button>
        
        <div className="flex gap-2">
          {!isAdmin && onToggleLike && (
            <Button 
              variant="outline"
              onClick={onToggleLike}
              className={`${
                isLiked 
                  ? 'border-red-500 text-red-500 hover:bg-red-50' 
                  : 'border-gray-300 text-gray-700 hover:bg-gray-50'
              }`}
            >
              <Heart className={`w-4 h-4 mr-2 ${isLiked ? 'fill-red-500' : ''}`} />
              {isLiked ? 'Hapus dari Favorit' : 'Simpan ke Favorit'}
            </Button>
          )}
          
          {isAdmin && onEdit && onDelete && (
            <>
              <Button 
                variant="outline"
                onClick={onEdit}
                className="border-blue-600 text-blue-600 hover:bg-blue-50"
              >
                Edit Resep
              </Button>
              <Button 
                variant="outline"
                onClick={onDelete}
                className="border-red-600 text-red-600 hover:bg-red-50"
              >
                Hapus Resep
              </Button>
            </>
          )}
        </div>
      </div>

      {/* Hero Image */}
      <div className="relative h-96 rounded-xl overflow-hidden mb-8">
        <ImageWithFallback
          src={recipe.image}
          alt={recipe.title}
          className="w-full h-full object-cover"
        />
      </div>

      <div className="grid md:grid-cols-3 gap-8">
        {/* Main Content */}
        <div className="md:col-span-2">
          <div className="mb-6">
            <div className="flex flex-wrap gap-2 mb-4">
              <Badge className="bg-orange-100 text-orange-700 hover:bg-orange-200">
                {recipe.category}
              </Badge>
              <Badge variant="outline" className="border-orange-200 text-orange-700">
                {recipe.region}
              </Badge>
            </div>

            <h1 className="mb-4">{recipe.title}</h1>

            <div className="flex items-center gap-6 text-gray-600 mb-4">
              <div className="flex items-center gap-2">
                <User className="w-5 h-5" />
                <span>{recipe.author}</span>
              </div>
              <div className="flex items-center gap-2">
                <Clock className="w-5 h-5" />
                <span>{recipe.cookingTime}</span>
              </div>
              <div className="flex items-center gap-2">
                <Star className="w-5 h-5 fill-yellow-400 text-yellow-400" />
                <span>{recipe.rating.toFixed(1)} ({recipe.reviewCount} review)</span>
              </div>
            </div>

            <p className="text-gray-600">{recipe.description}</p>
          </div>

          <Separator className="my-8" />

          {/* Ingredients */}
          <div className="mb-8">
            <h2 className="mb-4">Bahan-Bahan</h2>
            <Card>
              <CardContent className="p-6">
                <ul className="space-y-2">
                  {recipe.ingredients.map((ingredient, index) => (
                    <li key={index} className="flex items-start gap-3">
                      <div className="w-2 h-2 bg-orange-500 rounded-full mt-2 flex-shrink-0" />
                      <span>{ingredient}</span>
                    </li>
                  ))}
                </ul>
              </CardContent>
            </Card>
          </div>

          {/* Steps */}
          <div className="mb-8">
            <h2 className="mb-4">Langkah-Langkah</h2>
            <div className="space-y-4">
              {recipe.steps.map((step, index) => (
                <Card key={index}>
                  <CardContent className="p-6">
                    <div className="flex gap-4">
                      <div className="flex-shrink-0 w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center">
                        {index + 1}
                      </div>
                      <p className="flex-1">{step}</p>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>

          <Separator className="my-8" />

          {/* Reviews */}
          <div>
            <h2 className="mb-6">Review & Rating</h2>

            {/* Add Review */}
            <Card className="mb-6">
              <CardContent className="p-6">
                <h3 className="mb-4">Tulis Review</h3>
                
                <div className="mb-4">
                  <label className="block text-sm mb-2">Rating</label>
                  <div className="flex gap-2">
                    {[1, 2, 3, 4, 5].map((star) => (
                      <button
                        key={star}
                        onClick={() => setNewRating(star)}
                        className="focus:outline-none"
                      >
                        <Star
                          className={`w-6 h-6 ${
                            star <= newRating
                              ? 'fill-yellow-400 text-yellow-400'
                              : 'text-gray-300'
                          }`}
                        />
                      </button>
                    ))}
                  </div>
                </div>

                <Textarea
                  placeholder="Bagikan pengalaman Anda mencoba resep ini..."
                  value={newReview}
                  onChange={(e) => setNewReview(e.target.value)}
                  className="mb-4"
                  rows={4}
                />

                <Button 
                  onClick={handleSubmitReview}
                  className="bg-orange-600 hover:bg-orange-700"
                >
                  Kirim Review
                </Button>
              </CardContent>
            </Card>

            {/* Review List */}
            <div className="space-y-4">
              {recipe.reviews.map((review) => (
                <Card key={review.id}>
                  <CardContent className="p-6">
                    <div className="flex gap-4">
                      <Avatar>
                        <AvatarImage src={review.avatar} />
                        <AvatarFallback>{review.author[0]}</AvatarFallback>
                      </Avatar>
                      
                      <div className="flex-1">
                        <div className="flex items-center justify-between mb-2">
                          <span>{review.author}</span>
                          <span className="text-sm text-gray-500">{review.date}</span>
                        </div>
                        
                        <div className="flex gap-1 mb-2">
                          {[1, 2, 3, 4, 5].map((star) => (
                            <Star
                              key={star}
                              className={`w-4 h-4 ${
                                star <= review.rating
                                  ? 'fill-yellow-400 text-yellow-400'
                                  : 'text-gray-300'
                              }`}
                            />
                          ))}
                        </div>
                        
                        <p className="text-gray-600">{review.comment}</p>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        </div>

        {/* Sidebar */}
        <div>
          <Card className="sticky top-24">
            <CardContent className="p-6">
              <div className="flex items-center gap-3 mb-6">
                <Avatar className="w-12 h-12">
                  <AvatarImage src={recipe.authorAvatar} />
                  <AvatarFallback>{recipe.author[0]}</AvatarFallback>
                </Avatar>
                <div>
                  <p className="text-sm text-gray-500">Dibuat oleh</p>
                  <p>{recipe.author}</p>
                </div>
              </div>

              <Separator className="my-4" />

              <div className="space-y-4">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Clock className="w-5 h-5 text-orange-600" />
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Waktu Memasak</p>
                    <p>{recipe.cookingTime}</p>
                  </div>
                </div>

                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <ChefHat className="w-5 h-5 text-orange-600" />
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Kategori</p>
                    <p>{recipe.category}</p>
                  </div>
                </div>

                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Star className="w-5 h-5 text-orange-600" />
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Rating</p>
                    <p>{recipe.rating.toFixed(1)} / 5.0</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
