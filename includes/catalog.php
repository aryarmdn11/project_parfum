<?php
// Mengambil katalog produk (produk + varian + fragrance notes + rating) dari database.
function asset_url(?string $path): ?string {
    if (!$path) return null;
    return preg_match('#^(https?:)?//#', $path) ? $path : url($path);
}

function load_catalog(bool $onlyActive = true): array {
    $pdo = db();
    $where = $onlyActive ? "WHERE p.status_produk = 'aktif'" : '';
    $prods = $pdo->query("SELECT p.id_produk, p.nama_produk, p.deskripsi, p.gambar, p.konsentrasi, k.nama_kategori
                          FROM produk p JOIN kategori k ON k.id_kategori = p.id_kategori $where ORDER BY p.id_produk")->fetchAll();

    $variants = [];
    foreach ($pdo->query("SELECT id_variant, id_produk, ukuran_ml, harga, stok FROM produk_variants WHERE status_variant = 'aktif' ORDER BY id_produk, ukuran_ml") as $v) {
        $variants[$v['id_produk']][] = ['id' => (int)$v['id_variant'], 'size' => (int)$v['ukuran_ml'], 'price' => (float)$v['harga'], 'stock' => (int)$v['stok']];
    }
    $notes = [];
    foreach ($pdo->query("SELECT pn.id_produk, pn.note_stage, fn.nama_note FROM produk_notes pn JOIN fragrance_notes fn ON fn.id_note = pn.id_note ORDER BY pn.id_product_note") as $n) {
        $notes[$n['id_produk']][strtolower($n['note_stage'])][] = $n['nama_note'];
    }
    $ratings = [];
    foreach ($pdo->query('SELECT id_produk, AVG(rating) AS a, COUNT(*) AS c FROM reviews GROUP BY id_produk') as $r) {
        $ratings[$r['id_produk']] = ['avg' => round((float)$r['a'], 1), 'count' => (int)$r['c']];
    }

    $out = [];
    foreach ($prods as $p) {
        $id = (int)$p['id_produk'];
        if (empty($variants[$id])) continue;              // produk tanpa varian aktif tidak ditampilkan
        $img = $p['gambar'];
        $box = null;
        if ($img && str_contains($img, '-bottle')) {       // konvensi: foto box = nama-box-set.png
            $cand = str_replace('-bottle', '-box-set', $img);
            if (is_file(__DIR__ . '/../' . $cand)) $box = $cand;
        }
        $out[] = [
            'id' => $id, 'name' => $p['nama_produk'], 'desc' => (string)$p['deskripsi'],
            'type' => (string)$p['konsentrasi'], 'category' => $p['nama_kategori'],
            'image' => asset_url($img), 'box' => asset_url($box),
            'variants' => $variants[$id],
            'notes' => ['top' => $notes[$id]['top'] ?? [], 'middle' => $notes[$id]['middle'] ?? [], 'base' => $notes[$id]['base'] ?? []],
            'rating' => $ratings[$id] ?? ['avg' => 0, 'count' => 0],
        ];
    }
    return $out;
}
