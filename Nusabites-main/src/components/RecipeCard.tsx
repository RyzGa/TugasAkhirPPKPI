import { Clock, Star, User, Edit, Trash2, Heart } from 'lucide-react';
import { Card, CardContent } from './ui/card';
import { Badge } from './ui/badge';
import { Button } from './ui/button';
import { ImageWithFallback } from './figma/ImageWithFallback';

export interface Recipe {
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
}

interface RecipeCardProps {
  recipe: Recipe;
  onClick: () => void;
  isAdmin?: boolean;
  onEdit?: (e: React.MouseEvent) => void;
  onDelete?: (e: React.MouseEvent) => void;
  isLiked?: boolean;
  onToggleLike?: (e: React.MouseEvent) => void;
}

export function RecipeCard({ recipe, onClick, isAdmin = false, onEdit, onDelete, isLiked = false, onToggleLike }: RecipeCardProps) {
  return (
    <Card 
      className="overflow-hidden hover:shadow-lg transition-shadow duration-300 cursor-pointer group"
      onClick={onClick}
    >
      <div className="relative h-48 overflow-hidden">
        <ImageWithFallback
          src={recipe.image}
          alt={recipe.title}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
        <Badge className="absolute top-3 left-3 bg-white/90 text-gray-900 hover:bg-white">
          {recipe.category}
        </Badge>
        
        {isAdmin && onEdit && onDelete && (
          <div className="absolute top-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <Button
              size="sm"
              variant="secondary"
              className="h-8 w-8 p-0 bg-white/90 hover:bg-white"
              onClick={onEdit}
            >
              <Edit className="w-4 h-4 text-blue-600" />
            </Button>
            <Button
              size="sm"
              variant="secondary"
              className="h-8 w-8 p-0 bg-white/90 hover:bg-white"
              onClick={onDelete}
            >
              <Trash2 className="w-4 h-4 text-red-600" />
            </Button>
          </div>
        )}

        {!isAdmin && onToggleLike && (
          <Button
            size="sm"
            variant="secondary"
            className="absolute top-3 right-3 h-8 w-8 p-0 bg-white/90 hover:bg-white opacity-0 group-hover:opacity-100 transition-opacity"
            onClick={onToggleLike}
          >
            <Heart 
              className={`w-4 h-4 transition-colors ${
                isLiked ? 'fill-red-500 text-red-500' : 'text-gray-600'
              }`} 
            />
          </Button>
        )}
      </div>
      
      <CardContent className="p-4">
        <h3 className="mb-2 line-clamp-2 group-hover:text-orange-600 transition-colors">
          {recipe.title}
        </h3>
        
        <div className="flex items-center gap-2 text-gray-600 mb-3">
          <div className="flex items-center gap-1">
            <User className="w-4 h-4" />
            <span className="text-sm">{recipe.author}</span>
          </div>
        </div>

        <div className="flex items-center justify-between text-sm text-gray-600">
          <div className="flex items-center gap-1">
            <Clock className="w-4 h-4" />
            <span>{recipe.cookingTime}</span>
          </div>
          
          <div className="flex items-center gap-1">
            <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
            <span>{recipe.rating.toFixed(1)}</span>
            <span className="text-gray-400">({recipe.reviewCount})</span>
          </div>
        </div>

        <Badge variant="outline" className="mt-3 border-orange-200 text-orange-700">
          {recipe.region}
        </Badge>
      </CardContent>
    </Card>
  );
}
