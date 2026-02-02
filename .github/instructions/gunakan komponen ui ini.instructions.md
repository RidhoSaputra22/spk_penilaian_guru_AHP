---
applyTo: '**/*.php'
---
gunalan komponen UI ini dalam membuat antarmuka pengguna yang konsisten dan menarik. Komponen ini dirancang untuk memudahkan pengembangan front-end dengan menyediakan elemen-elemen siap pakai yang dapat disesuaikan sesuai kebutuhan proyek Anda.

Berikut adalah beberapa komponen UI yang dapat Anda gunakan:
recources/views/components/ui/alert.blade.php
recources/views/components/ui/button.blade.php
recources/views/components/ui/card.blade.php
recources/views/components/ui/modal.blade.php
recources/views/components/ui/input.blade.php
recources/views/components/ui/select.blade.php
recources/views/components/ui/checkbox.blade.php
recources/views/components/ui/radio.blade.php  
recources/views/components/ui/stat.blade.php  
recources/views/components/ui/table.blade.php  
recources/views/components/ui/textarea.blade.php  
recources/views/components/ui/toast.blade.php  


perhatikan cara penggunaan select component berikut:

```blade
<x-ui.select label="Pilih Kategori" name="category" :options="$categories" selected="{{ old('category', $selectedCategory) }}" />
``` 
