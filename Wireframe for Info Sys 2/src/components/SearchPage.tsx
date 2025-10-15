import { useState } from "react";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "./ui/tabs";
import { Input } from "./ui/input";
import { Button } from "./ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { ProductCard } from "./ProductCard";
import { Search, BookOpen, GraduationCap, Edit } from "lucide-react";
import { Alert, AlertDescription } from "./ui/alert";

const mockBooks = [
  {
    id: 1,
    title: "Introduction to Computer Science",
    author: "Jane Smith",
    isbn: "978-0-123456-78-9",
    required: true,
    purchasePrice: 129.99,
    rentalPrice: 45.99,
    imageUrl: "https://images.unsplash.com/photo-1725870475677-7dc91efe9f93?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx0ZXh0Ym9vayUyMGJvb2slMjBjb3ZlcnxlbnwxfHx8fDE3NTk5MzkwMzN8MA&ixlib=rb-4.1.0&q=80&w=1080",
  },
  {
    id: 2,
    title: "Calculus: Early Transcendentals",
    author: "James Stewart",
    isbn: "978-1-285-74155-0",
    required: true,
    purchasePrice: 159.99,
    rentalPrice: 59.99,
    imageUrl: "https://images.unsplash.com/photo-1588912914017-923900a34710?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxjb2xsZWdlJTIwdGV4dGJvb2t8ZW58MXx8fHwxNzU5OTMwOTI2fDA&ixlib=rb-4.1.0&q=80&w=1080",
  },
  {
    id: 3,
    title: "World History: Patterns of Interaction",
    author: "Roger Beck",
    isbn: "978-0-547-03719-6",
    required: false,
    purchasePrice: 89.99,
    rentalPrice: 35.99,
    imageUrl: "https://images.unsplash.com/photo-1752697589018-64fc6b19cc9a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxhY2FkZW1pYyUyMGJvb2t8ZW58MXx8fHwxNzU5OTIwODk3fDA&ixlib=rb-4.1.0&q=80&w=1080",
  },
  {
    id: 4,
    title: "Organic Chemistry",
    author: "Paula Yurkanis Bruice",
    isbn: "978-0-134-04216-4",
    required: true,
    purchasePrice: 199.99,
    rentalPrice: 75.99,
    imageUrl: "https://images.unsplash.com/photo-1725870475677-7dc91efe9f93?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx0ZXh0Ym9vayUyMGJvb2slMjBjb3ZlcnxlbnwxfHx8fDE3NTk5MzkwMzN8MA&ixlib=rb-4.1.0&q=80&w=1080",
  },
];

const mockCourses = [
  { code: "CS101", name: "Intro to Computer Science", professor: "Dr. Smith" },
  { code: "MATH201", name: "Calculus I", professor: "Prof. Johnson" },
  { code: "HIST150", name: "World History", professor: "Dr. Williams" },
  { code: "CHEM210", name: "Organic Chemistry", professor: "Prof. Davis" },
];

export function SearchPage() {
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedCourse, setSelectedCourse] = useState("");

  return (
    <div className="min-h-[calc(100vh-80px)] bg-background py-6 px-4">
      <div className="container mx-auto max-w-6xl">
        <Tabs defaultValue="courses" className="w-full">
          <TabsList className="grid w-full grid-cols-2 mb-6">
            <TabsTrigger value="courses" className="gap-2">
              <GraduationCap className="w-4 h-4" />
              <span className="hidden sm:inline">My Courses</span>
              <span className="sm:hidden">Courses</span>
            </TabsTrigger>
            <TabsTrigger value="search" className="gap-2">
              <Search className="w-4 h-4" />
              <span>Search</span>
            </TabsTrigger>
          </TabsList>

          {/* My Courses Tab */}
          <TabsContent value="courses" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Your Course Booklists</CardTitle>
                <CardDescription>
                  Access your registered courses and required materials directly - no external redirect needed
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <label htmlFor="course-select">Select a course:</label>
                  <Select value={selectedCourse} onValueChange={setSelectedCourse}>
                    <SelectTrigger id="course-select">
                      <SelectValue placeholder="Choose your course" />
                    </SelectTrigger>
                    <SelectContent>
                      {mockCourses.map((course) => (
                        <SelectItem key={course.code} value={course.code}>
                          {course.code} - {course.name} ({course.professor})
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {selectedCourse && (
                  <Alert>
                    <BookOpen className="h-4 w-4" />
                    <AlertDescription>
                      Showing booklist for <strong>{selectedCourse}</strong>
                    </AlertDescription>
                  </Alert>
                )}
              </CardContent>
            </Card>

            {/* Books for selected course */}
            {selectedCourse && (
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <h2>Required & Suggested Materials</h2>
                  {/* Admin placeholder */}
                  <Button variant="outline" size="sm" className="gap-2" disabled>
                    <Edit className="w-4 h-4" />
                    <span className="hidden sm:inline">Edit (Admin Only)</span>
                    <span className="sm:hidden">Edit</span>
                  </Button>
                </div>
                
                <Alert>
                  <AlertDescription className="text-xs">
                    <strong>For Staff:</strong> Book images and details can be easily updated via the content management system
                  </AlertDescription>
                </Alert>

                <div className="grid gap-4">
                  {mockBooks.slice(0, 2).map((book) => (
                    <ProductCard key={book.id} {...book} />
                  ))}
                </div>
              </div>
            )}
          </TabsContent>

          {/* Search Tab */}
          <TabsContent value="search" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Search for Books</CardTitle>
                <CardDescription>
                  Search by course code, professor name, or keyword
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex gap-2">
                  <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                      type="search"
                      placeholder="e.g., CS101, Dr. Smith, or 'calculus'"
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                      className="pl-10"
                    />
                  </div>
                  <Button>Search</Button>
                </div>

                <div className="flex flex-wrap gap-2">
                  <Button variant="outline" size="sm">Course: CS101</Button>
                  <Button variant="outline" size="sm">Prof: Johnson</Button>
                  <Button variant="outline" size="sm">Required Only</Button>
                  <Button variant="outline" size="sm">Available to Rent</Button>
                </div>
              </CardContent>
            </Card>

            {/* Search Results */}
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <h2>Search Results</h2>
                <p className="text-muted-foreground">{mockBooks.length} books found</p>
              </div>

              <div className="grid gap-4">
                {mockBooks.map((book) => (
                  <ProductCard key={book.id} {...book} />
                ))}
              </div>
            </div>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
