<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;

if (!isLoggedIn()) {
    die("Access denied. Please log in.");
}

$request_id = $_GET['id'] ?? null;
$format = strtolower($_GET['format'] ?? 'pdf');

if (!$request_id) {
    die("Invalid request ID.");
}

// Fetch main request data
$stmt_req = $pdo->prepare("SELECT r.*, u.name as requester_name FROM requests r JOIN users u ON r.requester_id = u.id WHERE r.id = ? AND r.status = 'approved'");
$stmt_req->execute([$request_id]);
$request = $stmt_req->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    die("Request not found, not approved, or you do not have permission to view it.");
}

// Fetch technician data for this request
$stmt_tech = $pdo->prepare("SELECT * FROM request_technicians WHERE request_id = ?");
$stmt_tech->execute([$request_id]);
$technicians = $stmt_tech->fetchAll(PDO::FETCH_ASSOC);

// ---- CONVERT DATE TO INDONESIAN FORMAT ----
function toIndonesianDate($date) {
    $months = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $parts = explode('-', $date);
    return $parts[2] . ' ' . $months[(int)$parts[1]] . ' ' . $parts[0];
}

$letter_date_indonesian = toIndonesianDate($request['letter_date']);
$current_date_indonesian = toIndonesianDate(date('Y-m-d'));


// ====================================================================================
// ============================= GENERATE HTML FOR PDF ================================
// ====================================================================================
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
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
        <p>353/UM.000/TA-0500/02-2025</p>
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


// ====================================================================================
// ============================= HANDLE THE DOWNLOAD ==================================
// ====================================================================================

$filename = "Surat_Tugas_" . str_replace('/', '_', $request['request_number']);

if ($format === 'pdf') {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($filename . ".pdf", ["Attachment" => true]);

} elseif ($format === 'docx') {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();
    \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment; filename={$filename}.docx");

    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');

} else {
    die("Invalid format specified.");
}
