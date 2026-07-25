# Jurnal Dosen Indonesia

A search engine for Indonesian academic journals that provides various search functionalities through the SerpApi service. This application is built with a Python FastAPI backend and a modern, responsive frontend using Bootstrap.

## Features

- **General Search:** Perform general searches on Google Scholar.
- **Author Search:** Get detailed information about a Google Scholar author, including their articles and citation metrics.
- **Case Law Search:** Search for specific case law documents on Google Scholar.
- **AI-Powered Search:** Use Google's AI mode to ask questions about an image.
- **Modern UI:** A clean, dark-themed, and responsive user interface built with Bootstrap.
- **Privacy-Focused:** The application acts as a proxy to SerpApi, ensuring that your search queries are not directly exposed to your browser or other third parties.

## Tech Stack

- **Backend:** Python, FastAPI
- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5
- **API:** SerpApi

## Prerequisites

- Python 3.10+
- pip

## Installation

1.  **Clone the repository (or download the source code):**
    ```bash
    git clone <repository-url>
    cd <repository-directory>
    ```

2.  **Create and activate a virtual environment:**
    - On Windows:
        ```bash
        python -m venv .venv
        .venv\Scripts\activate
        ```
    - On macOS/Linux:
        ```bash
        python3 -m venv .venv
        source .venv/bin/activate
        ```

3.  **Install the required packages:**
    ```bash
    pip install -r jurnal_dosen_indonesia/backend/requirements.txt
    ```

4.  **Set up your environment variables:**
    - Navigate to the `jurnal_dosen_indonesia/backend` directory.
    - Create a file named `.env` by copying the example file:
        ```bash
        # On Windows
        copy .env.example .env

        # On macOS/Linux
        cp .env.example .env
        ```
    - Open the `.env` file and add your SerpApi API key:
        ```
        SERPAPI_API_KEY="your_serpapi_api_key_here"
        ```

## Usage

1.  **Start the application:**
    - Make sure you are in the `jurnal_dosen_indonesia/backend` directory.
    - Run the following command to start the FastAPI server:
        ```bash
        uvicorn main:app --reload
        ```

2.  **Open the application in your browser:**
    - Navigate to `http://localhost:8000` in your web browser.

## API Endpoints

The application exposes the following API endpoints:

- `GET /api/search`: Performs a general search.
  - **Query Parameter:** `q` (string, required) - The search query.
- `GET /api/author/{author_id}`: Fetches author details.
  - **Path Parameter:** `author_id` (string, required) - The Google Scholar author ID.
- `GET /api/case_law/{case_id}`: Fetches case law details.
  - **Path Parameter:** `case_id` (string, required) - The Google Scholar case ID.
- `GET /api/ai_search`: Performs a Google AI Mode search.
  - **Query Parameters:**
    - `q` (string, required) - The question to ask.
    - `image_url` (string, required) - The URL of the image to analyze.
    - `subsequent_request_token` (string, optional) - For multi-turn conversations.
