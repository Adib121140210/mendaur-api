# Tabung Sampah API Integration Guide

## ✅ Backend Setup Complete!

### What's Been Done:

1. **Migration Fixed** - `tabung_sampah` table created with proper foreign keys
2. **Controller Updated** - File upload handling implemented
3. **CORS Enabled** - React app can now connect to the API
4. **Storage Linked** - Uploaded files are accessible at `/storage/uploads/sampah/`

---

## 🚀 API Endpoint

**POST** `http://127.0.0.1:8000/api/tabung-sampah`

### Request Format (FormData):

```javascript
const formData = new FormData();
formData.append('user_id', 1);              // required - ID user yang login
formData.append('jadwal_id', 1);            // required - ID jadwal penyetoran
formData.append('nama_lengkap', 'John Doe'); // required - string
formData.append('no_hp', '08123456789');     // required - string
formData.append('titik_lokasi', 'TPS 3R');   // required - string
formData.append('jenis_sampah', 'Organik');  // required - string
formData.append('foto_sampah', file);        // optional - image file (jpeg,jpg,png,gif, max 2MB)
```

### Success Response (201):

```json
{
  "status": "success",
  "message": "Setor sampah berhasil!",
  "data": {
    "id": 1,
    "user_id": 1,
    "jadwal_id": 1,
    "nama_lengkap": "John Doe",
    "no_hp": "08123456789",
    "titik_lokasi": "TPS 3R",
    "jenis_sampah": "Organik",
    "foto_sampah": "uploads/sampah/1234567890_photo.jpg",
    "created_at": "2025-11-14T03:19:29.000000Z",
    "updated_at": "2025-11-14T03:19:29.000000Z"
  }
}
```

### Error Response (422):

```json
{
  "message": "The user id field is required. (and 2 more errors)",
  "errors": {
    "user_id": ["The user id field is required."],
    "jadwal_id": ["The jadwal id field is required."]
  }
}
```

---

## 📝 React Integration Example

Your current React code is **almost perfect**! Here's an optimized version:

```javascript
const handleSubmit = async (e) => {
  e.preventDefault();
  
  // Validate form
  const validation = validate();
  if (Object.keys(validation).length > 0) {
    setErrors(validation);
    return;
  }

  setLoading(true);
  
  const data = new FormData();
  data.append("user_id", currentUserId); // Get from auth context
  data.append("jadwal_id", formData.jadwalId);
  data.append("nama_lengkap", formData.nama);
  data.append("no_hp", formData.noHp);
  data.append("titik_lokasi", formData.lokasi);
  data.append("jenis_sampah", formData.jenis);
  
  // Only append if file exists
  if (formData.foto) {
    data.append("foto_sampah", formData.foto);
  }

  try {
    const res = await fetch("http://127.0.0.1:8000/api/tabung-sampah", {
      method: "POST",
      body: data,
      // Don't set Content-Type header - browser will set it automatically with boundary
    });

    if (!res.ok) {
      const errorData = await res.json();
      throw new Error(errorData.message || "Terjadi kesalahan");
    }

    const result = await res.json();
    console.log("Respons backend:", result);

    if (result.status === "success") {
      alert(result.message || "Setor sampah berhasil!");
      onClose();
      // Optional: refresh data or redirect
    }
  } catch (err) {
    console.error("Error submit:", err);
    alert(err.message || "Gagal mengirim data");
  } finally {
    setLoading(false);
  }
};
```

---

## 🔧 Other Available Endpoints

### GET All Tabung Sampah
```
GET http://127.0.0.1:8000/api/tabung-sampah
```

### GET by User ID
```
GET http://127.0.0.1:8000/api/users/{user_id}/tabung-sampah
```

### GET Single Record
```
GET http://127.0.0.1:8000/api/tabung-sampah/{id}
```

### UPDATE Record
```
PUT/PATCH http://127.0.0.1:8000/api/tabung-sampah/{id}
```

### DELETE Record
```
DELETE http://127.0.0.1:8000/api/tabung-sampah/{id}
```

---

## 📸 Accessing Uploaded Images

Uploaded images can be accessed at:
```
http://127.0.0.1:8000/storage/{path_from_database}

Example:
http://127.0.0.1:8000/storage/uploads/sampah/1234567890_photo.jpg
```

In React, display images like:
```jsx
<img 
  src={`http://127.0.0.1:8000/storage/${data.foto_sampah}`} 
  alt="Foto Sampah" 
/>
```

---

## 🧪 Testing

1. **Test with HTML form:**
   Open `test-form.html` in your browser

2. **Test with Postman:**
   - Method: POST
   - URL: `http://127.0.0.1:8000/api/tabung-sampah`
   - Body: form-data
   - Add fields as shown above

3. **Check uploaded files:**
   ```
   storage/app/public/uploads/sampah/
   ```

---

## ⚠️ Important Notes

1. **File Validation:**
   - Accepted formats: jpeg, jpg, png, gif
   - Max size: 2MB
   - Field is optional

2. **Foreign Keys:**
   - `user_id` must exist in `users` table
   - `jadwal_id` must exist in `jadwal_penyetorans` table

3. **CORS:**
   - Currently allows all origins (`*`)
   - For production, update `config/cors.php` to allow only your frontend domain

---

## 🚀 Running the Server

```bash
# Start Laravel server
php artisan serve

# Server runs on: http://127.0.0.1:8000
```

---

## 📦 Database Seeder

Sample data is already seeded with:
- 2 users
- 2 jadwal records
- 2 tabung_sampah records
- 5 kategori_transaksi records

Re-seed if needed:
```bash
php artisan migrate:fresh --seed
```

---

Need help? Check the test form or let me know! 🎉
