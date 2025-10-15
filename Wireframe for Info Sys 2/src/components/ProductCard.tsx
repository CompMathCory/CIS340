import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Card, CardContent } from "./ui/card";
import { ImageWithFallback } from "./figma/ImageWithFallback";

interface ProductCardProps {
  title: string;
  author: string;
  isbn: string;
  required: boolean;
  purchasePrice: number;
  rentalPrice: number;
  imageUrl?: string;
}

export function ProductCard({
  title,
  author,
  isbn,
  required,
  purchasePrice,
  rentalPrice,
  imageUrl,
}: ProductCardProps) {
  return (
    <Card className="overflow-hidden hover:shadow-md transition-shadow">
      <CardContent className="p-0">
        <div className="flex flex-col sm:flex-row gap-4 p-4">
          {/* Book Image */}
          <div className="flex-shrink-0 w-full sm:w-24 md:w-32">
            <div className="relative aspect-[2/3] bg-muted rounded overflow-hidden">
              <ImageWithFallback
                src={imageUrl || ""}
                alt={`Cover image for ${title}`}
                className="w-full h-full object-cover"
              />
            </div>
          </div>

          {/* Book Details */}
          <div className="flex-1 space-y-2 min-w-0">
            <div className="space-y-1">
              {required ? (
                <Badge variant="destructive">Required</Badge>
              ) : (
                <Badge variant="secondary">Suggested</Badge>
              )}
              <h3 className="line-clamp-2">{title}</h3>
              <p className="text-muted-foreground">{author}</p>
              <p className="text-xs text-muted-foreground">ISBN: {isbn}</p>
            </div>

            {/* Pricing and Actions */}
            <div className="flex flex-col sm:flex-row gap-2 pt-2">
              <div className="flex-1 space-y-2">
                <div className="flex items-center justify-between gap-2">
                  <div>
                    <p className="text-xs text-muted-foreground">Purchase</p>
                    <p>${purchasePrice.toFixed(2)}</p>
                  </div>
                  <Button size="sm" variant="default">
                    Buy
                  </Button>
                </div>
                <div className="flex items-center justify-between gap-2">
                  <div>
                    <p className="text-xs text-muted-foreground">Rent</p>
                    <p>${rentalPrice.toFixed(2)}</p>
                  </div>
                  <Button size="sm" variant="outline">
                    Rent
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
