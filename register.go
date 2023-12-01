package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"

	"golang.org/x/oauth2"
	"golang.org/x/oauth2/google"

	_ "github.com/mattn/go-sqlite3"
)

var db *sql.DB

func handleGoogleLogin(w http.ResponseWriter, r *http.Request) {
	// OAuth login handling (same as previous code)
}

func handleGoogleCallback(w http.ResponseWriter, r *http.Request) {
	// OAuth callback handling (same as previous code)
}

func init() {
	var err error
	db, err = sql.Open("sqlite3", "test.db")
	if err != nil {
		panic("Failed to open database")
	}

	_, err = db.Exec(`CREATE TABLE IF NOT EXISTS users (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		google_id TEXT,
		email TEXT,
		name TEXT
	)`)
	if err != nil {
		panic("Failed to create table")
	}
}
