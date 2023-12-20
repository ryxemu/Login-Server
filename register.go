package main

import (
	"fmt"
	"net/http"

	"github.com/pquerna/otp/totp"
	"golang.org/x/crypto/bcrypt"
)

func registrationHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodPost {
		// Handle user registration logic here
		email := r.FormValue("email")
		password := r.FormValue("password")

		// Validate and hash the password
		_, err := bcrypt.GenerateFromPassword([]byte(password), bcrypt.DefaultCost)
		if err != nil {
			http.Error(w, "Failed to hash the password", http.StatusInternalServerError)
			return
		}

		// Save the user details (email and hashed password) to the database (replace this with your DB logic)
		// ...

		// Generate a secret for 2FA
		key, err := totp.Generate(totp.GenerateOpts{
			Issuer:      "YourAppName",
			AccountName: email, // User's email (or any identifier)
		})
		if err != nil {
			http.Error(w, "Failed to generate 2FA secret", http.StatusInternalServerError)
			return
		}

		// Get QR code URL for 2FA setup
		qrCode, err := key.Image(200, 200)
		if err != nil {
			http.Error(w, "Failed to generate QR code", http.StatusInternalServerError)
			return
		}
		qrCodeURL := fmt.Sprintf("data:image/png;base64,%s", qrCode)

		// Display the QR code for 2FA setup (you can render this in an HTML template)
		fmt.Fprintf(w, "<h1>Enable Two-Factor Authentication (2FA)</h1>")
		fmt.Fprintf(w, "<p>Scan the QR code below with an authenticator app:</p>")
		fmt.Fprintf(w, "<img src='%s' alt='QR Code'>", qrCodeURL)
	} else {
		// Display registration form (similar to the previous code)
		fmt.Fprintf(w, "<h1>User Registration</h1>")
		fmt.Fprintf(w, "<form method='post' action='/register'>")
		fmt.Fprintf(w, "Email: <input type='text' name='email'><br>")
		fmt.Fprintf(w, "Password: <input type='password' name='password'><br>")
		fmt.Fprintf(w, "<input type='submit' value='Register'>")
		fmt.Fprintf(w, "</form>")
	}
}


func main() {
	http.HandleFunc("/register", registrationHandler)
	http.ListenAndServe(":8080", nil)
}
