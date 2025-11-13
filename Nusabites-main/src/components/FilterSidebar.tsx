import { X } from 'lucide-react';
import { Button } from './ui/button';
import { Label } from './ui/label';
import { RadioGroup, RadioGroupItem } from './ui/radio-group';
import { Checkbox } from './ui/checkbox';
import { Separator } from './ui/separator';

export interface Filters {
  categories: string[];
  regions: string[];
  cookingTime: string;
  minRating: number;
}

interface FilterSidebarProps {
  filters: Filters;
  onFiltersChange: (filters: Filters) => void;
  onClose?: () => void;
  isMobile?: boolean;
}

const CATEGORIES = ['Makanan Utama', 'Camilan', 'Minuman'];
const REGIONS = ['Jawa', 'Sumatera', 'Bali', 'Kalimantan', 'Sulawesi', 'Papua'];
const COOKING_TIMES = [
  { value: 'all', label: 'Semua Waktu' },
  { value: '0-15', label: '< 15 menit' },
  { value: '15-30', label: '15-30 menit' },
  { value: '30-60', label: '30-60 menit' },
  { value: '60+', label: '> 1 jam' },
];
const RATINGS = [
  { value: 0, label: 'Semua Rating' },
  { value: 4, label: '4+ Bintang' },
  { value: 3, label: '3+ Bintang' },
];

export function FilterSidebar({ filters, onFiltersChange, onClose, isMobile }: FilterSidebarProps) {
  const toggleCategory = (category: string) => {
    const newCategories = filters.categories.includes(category)
      ? filters.categories.filter(c => c !== category)
      : [...filters.categories, category];
    onFiltersChange({ ...filters, categories: newCategories });
  };

  const toggleRegion = (region: string) => {
    const newRegions = filters.regions.includes(region)
      ? filters.regions.filter(r => r !== region)
      : [...filters.regions, region];
    onFiltersChange({ ...filters, regions: newRegions });
  };

  const resetFilters = () => {
    onFiltersChange({
      categories: [],
      regions: [],
      cookingTime: 'all',
      minRating: 0,
    });
  };

  return (
    <div className={`bg-white ${isMobile ? 'h-full overflow-y-auto' : 'rounded-lg border border-gray-200 p-6'}`}>
      <div className="flex items-center justify-between mb-6">
        <h2>Filter</h2>
        {isMobile && (
          <Button variant="ghost" size="icon" onClick={onClose}>
            <X className="w-5 h-5" />
          </Button>
        )}
      </div>

      {/* Category Filter */}
      <div className="mb-6">
        <h3 className="mb-3">Kategori Makanan</h3>
        <div className="space-y-3">
          {CATEGORIES.map((category) => (
            <div key={category} className="flex items-center gap-2">
              <Checkbox
                id={`category-${category}`}
                checked={filters.categories.includes(category)}
                onCheckedChange={() => toggleCategory(category)}
              />
              <Label htmlFor={`category-${category}`} className="cursor-pointer">
                {category}
              </Label>
            </div>
          ))}
        </div>
      </div>

      <Separator className="my-6" />

      {/* Region Filter */}
      <div className="mb-6">
        <h3 className="mb-3">Asal Daerah</h3>
        <div className="space-y-3">
          {REGIONS.map((region) => (
            <div key={region} className="flex items-center gap-2">
              <Checkbox
                id={`region-${region}`}
                checked={filters.regions.includes(region)}
                onCheckedChange={() => toggleRegion(region)}
              />
              <Label htmlFor={`region-${region}`} className="cursor-pointer">
                {region}
              </Label>
            </div>
          ))}
        </div>
      </div>

      <Separator className="my-6" />

      {/* Cooking Time Filter */}
      <div className="mb-6">
        <h3 className="mb-3">Waktu Memasak</h3>
        <RadioGroup
          value={filters.cookingTime}
          onValueChange={(value) => onFiltersChange({ ...filters, cookingTime: value })}
        >
          {COOKING_TIMES.map((time) => (
            <div key={time.value} className="flex items-center gap-2">
              <RadioGroupItem value={time.value} id={`time-${time.value}`} />
              <Label htmlFor={`time-${time.value}`} className="cursor-pointer">
                {time.label}
              </Label>
            </div>
          ))}
        </RadioGroup>
      </div>

      <Separator className="my-6" />

      {/* Rating Filter */}
      <div className="mb-6">
        <h3 className="mb-3">Rating Minimum</h3>
        <RadioGroup
          value={filters.minRating.toString()}
          onValueChange={(value) => onFiltersChange({ ...filters, minRating: Number(value) })}
        >
          {RATINGS.map((rating) => (
            <div key={rating.value} className="flex items-center gap-2">
              <RadioGroupItem value={rating.value.toString()} id={`rating-${rating.value}`} />
              <Label htmlFor={`rating-${rating.value}`} className="cursor-pointer">
                {rating.label}
              </Label>
            </div>
          ))}
        </RadioGroup>
      </div>

      <Button 
        variant="outline" 
        className="w-full"
        onClick={resetFilters}
      >
        Reset Filter
      </Button>
    </div>
  );
}
