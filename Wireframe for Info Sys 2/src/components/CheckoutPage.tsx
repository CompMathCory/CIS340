import { useState } from "react";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { RadioGroup, RadioGroupItem } from "./ui/radio-group";
import { Alert, AlertDescription } from "./ui/alert";
import { Separator } from "./ui/separator";
import { ShoppingCart, CreditCard, Wallet, GraduationCap, Lock, AlertCircle, Save } from "lucide-react";
import { Badge } from "./ui/badge";

const mockCartItems = [
  {
    id: 1,
    title: "Introduction to Computer Science",
    author: "Jane Smith",
    price: 45.99,
    type: "Rental",
  },
  {
    id: 2,
    title: "Calculus: Early Transcendentals",
    author: "James Stewart",
    price: 159.99,
    type: "Purchase",
  },
  {
    id: 3,
    title: "Organic Chemistry",
    author: "Paula Yurkanis Bruice",
    price: 75.99,
    type: "Rental",
  },
];

export function CheckoutPage() {
  const [paymentMethod, setPaymentMethod] = useState("");
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [address, setAddress] = useState("");

  const subtotal = mockCartItems.reduce((sum, item) => sum + item.price, 0);
  const tax = subtotal * 0.08;
  const total = subtotal + tax;

  return (
    <div className="min-h-[calc(100vh-80px)] bg-secondary/30 py-6 px-4">
      <div className="container mx-auto max-w-6xl">
        <h1 className="mb-6">Checkout</h1>

        <div className="grid lg:grid-cols-3 gap-6">
          {/* Main Checkout Form */}
          <div className="lg:col-span-2 space-y-6">
            {/* Cart Persistence Alert */}
            <Alert>
              <Save className="h-4 w-4" />
              <AlertDescription>
                Your cart items are saved. You can safely navigate away and return to complete your purchase.
              </AlertDescription>
            </Alert>

            {/* Shipping Information */}
            <Card>
              <CardHeader>
                <CardTitle>Shipping Information</CardTitle>
                <CardDescription>
                  This information will be saved for future orders
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid sm:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="firstName">First Name</Label>
                    <Input
                      id="firstName"
                      placeholder="John"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="lastName">Last Name</Label>
                    <Input id="lastName" placeholder="Doe" />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    type="email"
                    placeholder="student@university.edu"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="address">Street Address</Label>
                  <Input
                    id="address"
                    placeholder="123 Campus Drive"
                    value={address}
                    onChange={(e) => setAddress(e.target.value)}
                  />
                </div>

                <div className="grid sm:grid-cols-3 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="city">City</Label>
                    <Input id="city" placeholder="College Town" />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="state">State</Label>
                    <Input id="state" placeholder="CA" />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="zip">ZIP Code</Label>
                    <Input id="zip" placeholder="12345" />
                  </div>
                </div>

                <Alert variant="default" className="bg-blue-50 border-blue-200">
                  <AlertCircle className="h-4 w-4 text-blue-600" />
                  <AlertDescription className="text-blue-800">
                    Your shipping information is automatically saved and will be prefilled on your next order
                  </AlertDescription>
                </Alert>
              </CardContent>
            </Card>

            {/* Payment Method - CRITICAL SECTION */}
            <Card>
              <CardHeader>
                <CardTitle>Payment Method</CardTitle>
                <CardDescription>
                  Select how you'd like to pay for your order
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                {/* CRITICAL: Payment Options Clarity Box */}
                <Alert variant="default" className="bg-amber-50 border-amber-300">
                  <AlertCircle className="h-4 w-4 text-amber-600" />
                  <AlertDescription>
                    <strong className="text-amber-900">Understanding Payment Options:</strong>
                    <ul className="mt-2 space-y-1 text-amber-900 list-disc list-inside">
                      <li>
                        <strong>Student Account:</strong> Charges are added to your university tuition bill. Payment is due with your tuition at the end of the semester.
                      </li>
                      <li>
                        <strong>Credit/Debit Card:</strong> Immediate payment using your personal card. Charges appear on your card statement right away.
                      </li>
                      <li>
                        <strong>Digital Wallet:</strong> Pay instantly using Apple Pay, Google Pay, or PayPal.
                      </li>
                    </ul>
                  </AlertDescription>
                </Alert>

                <RadioGroup value={paymentMethod} onValueChange={setPaymentMethod}>
                  {/* Student Account Option */}
                  <div className="flex items-start space-x-3 p-4 border rounded-lg hover:bg-accent/50 transition-colors">
                    <RadioGroupItem value="student-account" id="student-account" className="mt-1" />
                    <div className="flex-1">
                      <Label htmlFor="student-account" className="flex items-center gap-2 cursor-pointer">
                        <GraduationCap className="w-5 h-5" />
                        Student Account (Billed with Tuition)
                      </Label>
                      <p className="text-sm text-muted-foreground mt-1">
                        Add charges to your university account - due with tuition payment
                      </p>
                    </div>
                  </div>

                  {/* Credit/Debit Card Option */}
                  <div className="flex items-start space-x-3 p-4 border rounded-lg hover:bg-accent/50 transition-colors">
                    <RadioGroupItem value="credit-card" id="credit-card" className="mt-1" />
                    <div className="flex-1">
                      <Label htmlFor="credit-card" className="flex items-center gap-2 cursor-pointer">
                        <CreditCard className="w-5 h-5" />
                        Credit or Debit Card
                      </Label>
                      <p className="text-sm text-muted-foreground mt-1">
                        Pay immediately with your card
                      </p>
                      
                      {paymentMethod === "credit-card" && (
                        <div className="mt-4 space-y-3">
                          <div className="space-y-2">
                            <Label htmlFor="card-number">Card Number</Label>
                            <Input id="card-number" placeholder="1234 5678 9012 3456" />
                          </div>
                          <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-2">
                              <Label htmlFor="expiry">Expiry Date</Label>
                              <Input id="expiry" placeholder="MM/YY" />
                            </div>
                            <div className="space-y-2">
                              <Label htmlFor="cvc">CVC</Label>
                              <Input id="cvc" placeholder="123" />
                            </div>
                          </div>
                        </div>
                      )}
                    </div>
                  </div>

                  {/* Digital Wallet Option */}
                  <div className="flex items-start space-x-3 p-4 border rounded-lg hover:bg-accent/50 transition-colors">
                    <RadioGroupItem value="digital-wallet" id="digital-wallet" className="mt-1" />
                    <div className="flex-1">
                      <Label htmlFor="digital-wallet" className="flex items-center gap-2 cursor-pointer">
                        <Wallet className="w-5 h-5" />
                        Digital Wallet
                      </Label>
                      <p className="text-sm text-muted-foreground mt-1">
                        Apple Pay, Google Pay, or PayPal
                      </p>
                      
                      {paymentMethod === "digital-wallet" && (
                        <div className="mt-4 flex flex-wrap gap-2">
                          <Button variant="outline" size="sm">Apple Pay</Button>
                          <Button variant="outline" size="sm">Google Pay</Button>
                          <Button variant="outline" size="sm">PayPal</Button>
                        </div>
                      )}
                    </div>
                  </div>
                </RadioGroup>

                {/* Security Indicator */}
                <Alert>
                  <Lock className="h-4 w-4" />
                  <AlertDescription>
                    <strong>Secure Checkout:</strong> All payment information is encrypted using industry-standard SSL/TLS encryption. Your data is never stored on our servers.
                  </AlertDescription>
                </Alert>
              </CardContent>
            </Card>

            {/* Place Order Button */}
            <Button className="w-full" size="lg" disabled={!paymentMethod}>
              {paymentMethod ? "Place Order" : "Select Payment Method to Continue"}
            </Button>
          </div>

          {/* Order Summary Sidebar */}
          <div className="lg:col-span-1">
            <Card className="sticky top-24">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <ShoppingCart className="w-5 h-5" />
                  Order Summary
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {/* Cart Items */}
                <div className="space-y-3">
                  {mockCartItems.map((item) => (
                    <div key={item.id} className="flex justify-between items-start gap-2">
                      <div className="flex-1 min-w-0">
                        <p className="line-clamp-1">{item.title}</p>
                        <p className="text-xs text-muted-foreground">{item.author}</p>
                        <Badge variant="outline" className="text-xs mt-1">
                          {item.type}
                        </Badge>
                      </div>
                      <p className="flex-shrink-0">${item.price.toFixed(2)}</p>
                    </div>
                  ))}
                </div>

                <Separator />

                {/* Totals */}
                <div className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span>Subtotal</span>
                    <span>${subtotal.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span>Tax (8%)</span>
                    <span>${tax.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span>Shipping</span>
                    <span className="text-green-600">FREE</span>
                  </div>
                  <Separator />
                  <div className="flex justify-between">
                    <span>Total</span>
                    <span>${total.toFixed(2)}</span>
                  </div>
                </div>

                <Alert className="mt-4">
                  <AlertCircle className="h-4 w-4" />
                  <AlertDescription className="text-xs">
                    Cart items remain saved even if you close this page
                  </AlertDescription>
                </Alert>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
}
