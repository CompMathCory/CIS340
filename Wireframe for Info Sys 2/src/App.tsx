import { useState } from "react";
import { Header } from "./components/Header";
import { LoginPage } from "./components/LoginPage";
import { SearchPage } from "./components/SearchPage";
import { CheckoutPage } from "./components/CheckoutPage";

export default function App() {
  const [currentPage, setCurrentPage] = useState<"login" | "search" | "checkout">("search");

  const renderPage = () => {
    switch (currentPage) {
      case "login":
        return <LoginPage />;
      case "search":
        return <SearchPage />;
      case "checkout":
        return <CheckoutPage />;
      default:
        return <SearchPage />;
    }
  };

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Header onNavigate={setCurrentPage} currentPage={currentPage} />
      <main className="flex-1">
        {renderPage()}
      </main>
      <footer className="border-t py-6 px-4 bg-card">
        <div className="container mx-auto max-w-6xl">
          <div className="grid sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div>
              <h3 className="mb-2">Help & Support</h3>
              <ul className="space-y-1 text-sm text-muted-foreground">
                <li><a href="#" className="hover:underline">Contact Us</a></li>
                <li><a href="#" className="hover:underline">FAQs</a></li>
                <li><a href="#" className="hover:underline">Accessibility</a></li>
              </ul>
            </div>
            <div>
              <h3 className="mb-2">Policies</h3>
              <ul className="space-y-1 text-sm text-muted-foreground">
                <li><a href="#" className="hover:underline">Return Policy</a></li>
                <li><a href="#" className="hover:underline">Rental Terms</a></li>
                <li><a href="#" className="hover:underline">Privacy Policy</a></li>
              </ul>
            </div>
            <div>
              <h3 className="mb-2">Resources</h3>
              <ul className="space-y-1 text-sm text-muted-foreground">
                <li><a href="#" className="hover:underline">Store Hours</a></li>
                <li><a href="#" className="hover:underline">Buyback Program</a></li>
                <li><a href="#" className="hover:underline">Price Match</a></li>
              </ul>
            </div>
            <div>
              <h3 className="mb-2">Account</h3>
              <ul className="space-y-1 text-sm text-muted-foreground">
                <li><a href="#" className="hover:underline">Order History</a></li>
                <li><a href="#" className="hover:underline">My Courses</a></li>
                <li><a href="#" className="hover:underline">Saved Items</a></li>
              </ul>
            </div>
          </div>
          <div className="mt-6 pt-6 border-t text-center text-sm text-muted-foreground">
            <p>© 2025 Campus Bookstore. All content optimized for keyboard navigation and screen readers.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
