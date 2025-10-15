import { Search, ShoppingCart, User, Book } from "lucide-react";
import { Button } from "./ui/button";
import { Input } from "./ui/input";

interface HeaderProps {
  onNavigate: (page: string) => void;
  currentPage: string;
}

export function Header({ onNavigate, currentPage }: HeaderProps) {
  return (
    <header className="w-full border-b bg-card sticky top-0 z-50">
      <div className="container mx-auto px-4 py-3">
        <div className="flex items-center justify-between gap-4">
          {/* Logo */}
          <button
            onClick={() => onNavigate("search")}
            className="flex items-center gap-2 hover:opacity-80 transition-opacity"
            aria-label="Campus Bookstore Home"
          >
            <Book className="w-6 h-6 md:w-8 md:h-8" />
            <span className="hidden sm:block">Campus Bookstore</span>
          </button>

          {/* Desktop Search Bar */}
          <div className="hidden md:flex items-center flex-1 max-w-xl mx-4">
            <div className="relative w-full">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input
                type="search"
                placeholder="Search by course, professor, or keyword..."
                className="pl-10"
              />
            </div>
          </div>

          {/* Actions */}
          <div className="flex items-center gap-2 md:gap-3">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => onNavigate("search")}
              className="md:hidden"
              aria-label="Search"
            >
              <Search className="w-5 h-5" />
            </Button>
            
            <Button
              variant="ghost"
              size="icon"
              onClick={() => onNavigate("checkout")}
              className="relative"
              aria-label="Shopping Cart"
            >
              <ShoppingCart className="w-5 h-5" />
              <span className="absolute -top-1 -right-1 bg-primary text-primary-foreground rounded-full w-5 h-5 flex items-center justify-center text-xs">
                3
              </span>
            </Button>
            
            <Button
              variant="ghost"
              size="icon"
              onClick={() => onNavigate("login")}
              aria-label="Account"
            >
              <User className="w-5 h-5" />
            </Button>
          </div>
        </div>

        {/* Mobile Search Bar */}
        <div className="md:hidden mt-3">
          <div className="relative w-full">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input
              type="search"
              placeholder="Search courses, professors, keywords..."
              className="pl-10"
            />
          </div>
        </div>
      </div>
    </header>
  );
}
