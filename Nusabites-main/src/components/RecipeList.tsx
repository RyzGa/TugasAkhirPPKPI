import { RecipeCard, Recipe } from './RecipeCard';
import { Card, CardContent } from './ui/card';
import { ChefHat } from 'lucide-react';

interface RecipeListProps {
  recipes: Recipe[];
  onRecipeClick: (id: string) => void;
  isAdmin?: boolean;
  onEdit?: (id: string) => void;
  onDelete?: (id: string) => void;
  likedRecipeIds?: string[];
  onToggleLike?: (id: string) => void;
}

export function RecipeList({ recipes, onRecipeClick, isAdmin = false, onEdit, onDelete, likedRecipeIds = [], onToggleLike }: RecipeListProps) {
  if (recipes.length === 0) {
    return (
      <Card>
        <CardContent className="p-12 text-center">
          <ChefHat className="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 className="mb-2">Tidak ada resep ditemukan</h3>
          <p className="text-gray-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {recipes.map((recipe) => (
        <RecipeCard
          key={recipe.id}
          recipe={recipe}
          onClick={() => onRecipeClick(recipe.id)}
          isAdmin={isAdmin}
          onEdit={isAdmin && onEdit ? (e) => {
            e.stopPropagation();
            onEdit(recipe.id);
          } : undefined}
          onDelete={isAdmin && onDelete ? (e) => {
            e.stopPropagation();
            onDelete(recipe.id);
          } : undefined}
          isLiked={likedRecipeIds.includes(recipe.id)}
          onToggleLike={onToggleLike ? (e) => {
            e.stopPropagation();
            onToggleLike(recipe.id);
          } : undefined}
        />
      ))}
    </div>
  );
}
