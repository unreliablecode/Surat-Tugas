<?php
require_once 'config.php';

// SECURITY: Only admins can access this preview
if (!isAdmin()) {
    die("Access Denied.");
}

$request_id = $_GET['id'] ?? null;
if (!$request_id) {
    die("Invalid request ID.");
}

// Fetch main request data
$stmt_req = $pdo->prepare("SELECT r.*, u.name as requester_name FROM requests r JOIN users u ON r.requester_id = u.id WHERE r.id = ?");
$stmt_req->execute([$request_id]);
$request = $stmt_req->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    die("Request not found.");
}

// Fetch technician data
$stmt_tech = $pdo->prepare("SELECT * FROM request_technicians WHERE request_id = ?");
$stmt_tech->execute([$request_id]);
$technicians = $stmt_tech->fetchAll(PDO::FETCH_ASSOC);

function toIndonesianDate($date) {
    $months = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $parts = explode('-', $date);
    return $parts[2] . ' ' . $months[(int)$parts[1]] . ' ' . $parts[0];
}

$letter_date_indonesian = toIndonesianDate($request['letter_date']);
// Use the letter's creation date for the signature part
$current_date_indonesian = toIndonesianDate(date('Y-m-d', strtotime($request['created_at'])));


// Generate the same HTML as the download file
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<title>Preview Surat Tugas</title>
<style>
    body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; margin: 1.5cm; }
    .header, .footer { text-align: center; }
    .header h2 { margin: 0; padding: 0; font-size: 14pt; font-weight: bold; text-decoration: underline; }
    .header p { margin: 0; padding: 0; }
    .content { margin-top: 30px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid black; padding: 8px; text-align: left; }
    th { text-align: center; }
    .signature { margin-top: 50px; }
</style>
</head>
<body>
    <div class="header">
        <h2>SURAT TUGAS</h2>
        <p>{$request['request_number']}</p>
    </div>

    <div class="content">
        <p>Pada hari ini, {$letter_date_indonesian}, PT TELKOM AKSES Jalan Sudirman No. 199, Simpang Tiga, Pekanbaru, memberikan tugas kepada:</p>

        <table>
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NIK</th>
                    <th>NAMA</th>
                    <th>POSISI</th>
                    <th>PSA</th>
                </tr>
            </thead>
            <tbody>
HTML;

$no = 1;
foreach ($technicians as $tech) {
    $html .= "<tr>";
    $html .= "<td style='text-align:center;'>{$no}</td>";
    $html .= "<td>" . htmlspecialchars($tech['nik']) . "</td>";
    $html .= "<td>" . htmlspecialchars($tech['name']) . "</td>";
    $html .= "<td>" . htmlspecialchars($tech['position']) . "</td>";
    $html .= "<td>" . htmlspecialchars($tech['psa']) . "</td>";
    $html .= "</tr>";
    $no++;
}

$html .= <<<HTML
            </tbody>
        </table>

        <p style="margin-top: 20px;">{$request['purpose_text']}</p>
        <p>Demikian Surat Tugas ini dibuat dan dapat digunakan sebagaimana mestinya.</p>
        
        <div class="signature">
            <p style="text-align: left; margin-left: 60%;">Pekanbaru, {$current_date_indonesian}</p>
            <p style="text-align: left; margin-left: 60%; margin-top: -10px;">Mgr. Shared Service Sumatera Bagian Tengah</p>
            <br><br><br>
            <p style="text-align: left; margin-left: 60%;"><strong><u>FX. SIGIT EKO PRAYOGO</u></strong></p>
            <p style="text-align: left; margin-left: 60%; margin-top: -10px;">NIK. 990535</p>
        </div>
    </div>
</body>
</html>
HTML;

echo $html;
?>
