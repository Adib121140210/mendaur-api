# 📰 Artikel API - Frontend Integration Guide

## ✅ Backend Status: **READY**

The artikel system has been successfully seeded with 8 comprehensive articles about waste management, recycling, and environmental topics.

---

## 🔌 API Endpoints

### 1. **Get All Articles**
```
GET /api/artikel
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "judul": "5 Cara Mudah Memilah Sampah di Rumah",
      "slug": "5-cara-mudah-memilah-sampah-di-rumah",
      "konten": "Full article content here...",
      "foto_cover": null,
      "penulis": "Tim Mendaur",
      "kategori": "Tips & Trik",
      "tanggal_publikasi": "2024-11-01T00:00:00.000000Z",
      "views": 245,
      "created_at": "2025-11-17T03:44:52.000000Z",
      "updated_at": "2025-11-17T03:44:52.000000Z"
    },
    // ... more articles
  ]
}
```

**Sorting:** Articles are sorted by `tanggal_publikasi` DESC (newest first)

---

### 2. **Get Single Article by Slug**
```
GET /api/artikel/{slug}
```

**Example:**
```
GET /api/artikel/5-cara-mudah-memilah-sampah-di-rumah
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "judul": "5 Cara Mudah Memilah Sampah di Rumah",
    "slug": "5-cara-mudah-memilah-sampah-di-rumah",
    "konten": "Full article content...",
    "foto_cover": null,
    "penulis": "Tim Mendaur",
    "kategori": "Tips & Trik",
    "tanggal_publikasi": "2024-11-01T00:00:00.000000Z",
    "views": 246,
    "created_at": "2025-11-17T03:44:52.000000Z",
    "updated_at": "2025-11-17T03:44:52.000000Z"
  }
}
```

**Note:** Views are automatically incremented when article is accessed!

**Error Response (404):**
```json
{
  "status": "error",
  "message": "Artikel tidak ditemukan"
}
```

---

## 📚 Available Articles (8 Total)

| No | Title | Category | Author | Date | Views |
|----|-------|----------|--------|------|-------|
| 1 | 5 Cara Mudah Memilah Sampah di Rumah | Tips & Trik | Tim Mendaur | 2024-11-01 | 245 |
| 2 | Manfaat Daur Ulang Plastik untuk Lingkungan | Edukasi | Dr. Budi Santoso | 2024-11-05 | 892 |
| 3 | Kisah Sukses Bank Sampah Sumber Rejeki | Inspirasi | Rina Wijaya | 2024-11-10 | 1563 |
| 4 | Mengubah Sampah Organik Menjadi Kompos Berkualitas | Tutorial | Agus Hermawan | 2024-11-12 | 678 |
| 5 | Dampak Sampah Plastik Terhadap Ekosistem Laut | Lingkungan | Dr. Maria Kusuma | 2024-11-14 | 2341 |
| 6 | Kreasi DIY: Membuat Pot Tanaman dari Botol Plastik | DIY | Dewi Lestari | 2024-11-15 | 1127 |
| 7 | Regulasi Terbaru: Kebijakan Pengurangan Sampah Plastik di Indonesia | Berita | Andi Prasetyo | 2024-11-16 | 445 |
| 8 | 10 Manfaat Ekonomi dari Pengelolaan Sampah yang Baik | Ekonomi | Siti Nurhaliza | 2024-11-17 | 156 |

---

## 📋 Categories Available

- **Tips & Trik** - Practical tips for waste management
- **Edukasi** - Educational content about recycling
- **Inspirasi** - Success stories and inspiring cases
- **Tutorial** - Step-by-step guides
- **Lingkungan** - Environmental impact articles
- **DIY** - Do-it-yourself creative projects
- **Berita** - News and regulations
- **Ekonomi** - Economic benefits of waste management

---

## 💻 Frontend Implementation

### **1. Display Article List**

```javascript
import React, { useState, useEffect } from 'react';

export default function ArtikelList() {
  const [articles, setArticles] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchArticles();
  }, []);

  const fetchArticles = async () => {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/artikel');
      const data = await response.json();
      
      if (data.status === 'success') {
        setArticles(data.data);
      }
    } catch (error) {
      console.error('Error fetching articles:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="artikel-list">
      <h1>Artikel & Tips Daur Ulang</h1>
      
      {loading ? (
        <div>Loading...</div>
      ) : (
        <div className="articles-grid">
          {articles.map(article => (
            <div key={article.id} className="article-card">
              <div className="article-header">
                <span className="category-badge">{article.kategori}</span>
                <span className="views">👁️ {article.views}</span>
              </div>
              
              <h3>{article.judul}</h3>
              
              <div className="article-meta">
                <span>✍️ {article.penulis}</span>
                <span>📅 {new Date(article.tanggal_publikasi).toLocaleDateString('id-ID')}</span>
              </div>
              
              <p className="excerpt">
                {article.konten.substring(0, 150)}...
              </p>
              
              <a 
                href={`/artikel/${article.slug}`} 
                className="read-more-btn"
              >
                Baca Selengkapnya →
              </a>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
```

---

### **2. Display Single Article**

```javascript
import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';

export default function ArtikelDetail() {
  const { slug } = useParams();
  const [article, setArticle] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchArticle();
  }, [slug]);

  const fetchArticle = async () => {
    try {
      const response = await fetch(`http://127.0.0.1:8000/api/artikel/${slug}`);
      const data = await response.json();
      
      if (data.status === 'success') {
        setArticle(data.data);
      } else {
        setError(data.message);
      }
    } catch (error) {
      console.error('Error fetching article:', error);
      setError('Gagal memuat artikel');
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div className="error">{error}</div>;
  if (!article) return <div>Artikel tidak ditemukan</div>;

  return (
    <article className="artikel-detail">
      {/* Header */}
      <div className="article-header">
        <span className="category-badge">{article.kategori}</span>
        <h1>{article.judul}</h1>
        
        <div className="article-meta">
          <span>✍️ {article.penulis}</span>
          <span>📅 {new Date(article.tanggal_publikasi).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
          })}</span>
          <span>👁️ {article.views} views</span>
        </div>
      </div>

      {/* Cover Image (if available) */}
      {article.foto_cover && (
        <img 
          src={`http://127.0.0.1:8000/storage/${article.foto_cover}`} 
          alt={article.judul}
          className="article-cover"
        />
      )}

      {/* Content */}
      <div className="article-content">
        {article.konten.split('\n').map((paragraph, index) => (
          <p key={index}>{paragraph}</p>
        ))}
      </div>

      {/* Footer */}
      <div className="article-footer">
        <button onClick={() => window.history.back()}>
          ← Kembali ke Daftar Artikel
        </button>
      </div>
    </article>
  );
}
```

---

### **3. Filter by Category**

```javascript
const [selectedCategory, setSelectedCategory] = useState('all');
const categories = ['all', 'Tips & Trik', 'Edukasi', 'Inspirasi', 'Tutorial', 'Lingkungan', 'DIY', 'Berita', 'Ekonomi'];

const filteredArticles = selectedCategory === 'all' 
  ? articles 
  : articles.filter(article => article.kategori === selectedCategory);

return (
  <div>
    {/* Category Filter */}
    <div className="category-filter">
      {categories.map(cat => (
        <button
          key={cat}
          className={selectedCategory === cat ? 'active' : ''}
          onClick={() => setSelectedCategory(cat)}
        >
          {cat === 'all' ? 'Semua' : cat}
        </button>
      ))}
    </div>

    {/* Articles */}
    <div className="articles-grid">
      {filteredArticles.map(article => (
        // ... article card
      ))}
    </div>
  </div>
);
```

---

### **4. Search Functionality**

```javascript
const [searchQuery, setSearchQuery] = useState('');

const searchedArticles = articles.filter(article => 
  article.judul.toLowerCase().includes(searchQuery.toLowerCase()) ||
  article.konten.toLowerCase().includes(searchQuery.toLowerCase()) ||
  article.penulis.toLowerCase().includes(searchQuery.toLowerCase())
);

return (
  <div>
    <input
      type="text"
      placeholder="Cari artikel..."
      value={searchQuery}
      onChange={(e) => setSearchQuery(e.target.value)}
      className="search-input"
    />
    
    {searchedArticles.map(article => (
      // ... article card
    ))}
  </div>
);
```

---

### **5. Popular Articles Widget**

```javascript
export default function PopularArticles() {
  const [popular, setPopular] = useState([]);

  useEffect(() => {
    fetch('http://127.0.0.1:8000/api/artikel')
      .then(res => res.json())
      .then(data => {
        // Sort by views DESC and take top 5
        const topArticles = data.data
          .sort((a, b) => b.views - a.views)
          .slice(0, 5);
        setPopular(topArticles);
      });
  }, []);

  return (
    <div className="popular-articles-widget">
      <h3>📈 Artikel Populer</h3>
      <ul>
        {popular.map(article => (
          <li key={article.id}>
            <a href={`/artikel/${article.slug}`}>
              {article.judul}
            </a>
            <span className="views">👁️ {article.views}</span>
          </li>
        ))}
      </ul>
    </div>
  );
}
```

---

## 🎨 CSS Styling Examples

```css
/* Article Card */
.article-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.article-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.category-badge {
  display: inline-block;
  padding: 4px 12px;
  background: #10b981;
  color: white;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.article-meta {
  display: flex;
  gap: 16px;
  color: #6b7280;
  font-size: 14px;
  margin: 12px 0;
}

.read-more-btn {
  display: inline-block;
  padding: 10px 20px;
  background: #059669;
  color: white;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: background 0.2s;
}

.read-more-btn:hover {
  background: #047857;
}

/* Article Detail */
.artikel-detail {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 20px;
}

.article-content {
  font-size: 18px;
  line-height: 1.8;
  color: #374151;
}

.article-content p {
  margin-bottom: 20px;
}

.article-cover {
  width: 100%;
  height: 400px;
  object-fit: cover;
  border-radius: 12px;
  margin: 20px 0;
}
```

---

## 🔥 Key Features

✅ **8 Pre-seeded Articles** - Real content about waste management  
✅ **Auto View Counter** - Views increment automatically  
✅ **SEO-Friendly Slugs** - Clean URLs for each article  
✅ **Category System** - 8 different categories  
✅ **Author Attribution** - Each article has an author  
✅ **Publication Dates** - Proper date tracking  
✅ **Full-Text Content** - Long-form educational content  
✅ **No Authentication Required** - Public access for reading  

---

## 📱 Mobile Responsive Tips

```css
@media (max-width: 768px) {
  .articles-grid {
    grid-template-columns: 1fr;
  }
  
  .article-meta {
    flex-direction: column;
    gap: 8px;
  }
  
  .category-filter {
    overflow-x: auto;
    white-space: nowrap;
  }
}
```

---

## 🚀 Next Steps for Frontend

1. **Create Article List Page** - Show all articles with filters
2. **Create Article Detail Page** - Full article view with routing (`/artikel/:slug`)
3. **Add Search Bar** - Filter articles by title/content
4. **Implement Category Filter** - Filter by 8 categories
5. **Add Popular Articles Widget** - Show top 5 by views
6. **Add Recent Articles Section** - Show latest 3-5 articles on homepage
7. **Create Article Card Component** - Reusable card design
8. **Add Share Buttons** - WhatsApp, Facebook, Twitter sharing
9. **Implement Reading Time** - Calculate based on word count
10. **Add Related Articles** - Show similar articles by category

---

## 💡 Tips for Better UX

- Show article excerpt (first 150 characters) on list view
- Display read time estimate: `Math.ceil(konten.split(' ').length / 200)` minutes
- Add breadcrumb navigation: Home > Artikel > Category > Title
- Implement infinite scroll or pagination for better performance
- Add "Back to Top" button for long articles
- Show related articles at bottom of article detail
- Add social sharing buttons
- Implement article bookmarking for logged-in users
- Add print-friendly view
- Show article publication date in relative format ("2 hari yang lalu")

---

## ✅ Backend Ready!

All artikel endpoints are **LIVE** and **TESTED**. You can start implementing the frontend immediately!

**Test URLs:**
- List: http://127.0.0.1:8000/api/artikel
- Detail: http://127.0.0.1:8000/api/artikel/5-cara-mudah-memilah-sampah-di-rumah

🎉 **Happy Coding!**
