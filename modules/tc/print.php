<?php
// modules/tc/print.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

require_once '../../config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("TC ID is required.");
}

$tc_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.date_of_leaving, t.reason_for_leaving, t.created_at as issue_date,
            s.admission_no, s.first_name, s.last_name, s.date_of_birth, s.father_name, s.mother_name, s.admission_date, s.current_class, s.current_section, s.gender, s.address,
            u.full_name as issuer_name
        FROM tc_records t
        JOIN students s ON t.student_id = s.id
        LEFT JOIN users u ON t.generated_by = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$tc_id]);
    $tc = $stmt->fetch();
    
    if(!$tc) die("Transfer Certificate not found.");
    
} catch(PDOException $e) {
    die("Database Error.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TC - <?php echo htmlspecialchars($tc['first_name'] . ' ' . $tc['last_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #525659; font-family: 'Times New Roman', Times, serif; }
        .tc-container {
            width: 210mm;
            min-height: 297mm;
            background: white;
            padding: 20mm;
            margin: 20px auto;
            position: relative;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .school-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .tc-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        .tc-details {
            font-size: 16px;
            line-height: 2;
        }
        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }
        .detail-label {
            width: 300px;
            font-weight: normal;
        }
        .detail-value {
            flex-grow: 1;
            border-bottom: 1px dotted #000;
            padding-left: 10px;
            font-weight: bold;
        }
        .signatures {
            margin-top: 100px;
            display: flex;
            justify-content: space-between;
        }
        .sig-block { text-align: center; }
        .sig-line { width: 200px; border-top: 1px solid #000; display: inline-block; padding-top: 5px; }
        
        @media print {
            body { background: white; margin: 0; }
            .tc-container { box-shadow: none; margin: 0; width: 100%; height: 100%; padding: 15mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="text-center mt-3 mb-0 no-print">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-2"></i> Print Certificate</button>
    <button onclick="window.close()" class="btn btn-secondary ms-2">Close</button>
</div>

<div class="tc-container">
    <div class="school-header">
        <h1 style="font-size: 32px; font-weight: bold; color: darkblue; margin-bottom: 5px;">GLOBAL STANDARD HIGH SCHOOL</h1>
        <p style="margin: 0; font-size: 14px;">Affiliation No: 1234567 | School Code: 89012</p>
        <p style="margin: 0; font-size: 14px;">123 Education Lane, Academic City, State - 100001</p>
        <p style="margin: 0; font-size: 14px;">Tel: (123) 456-7890 | Email: info@gshs.edu</p>
    </div>
    
    <div class="tc-title">Transfer Certificate</div>
    
    <div class="d-flex justify-content-between mb-4">
        <div><strong>TC No:</strong> <?php echo str_pad($tc_id, 5, '0', STR_PAD_LEFT); ?></div>
        <div><strong>Admission No:</strong> <?php echo htmlspecialchars($tc['admission_no']); ?></div>
    </div>
    
    <div class="tc-details">
        <div class="detail-row">
            <span class="detail-label">1. Name of the Pupil</span>
            <span class="detail-value text-uppercase"><?php echo htmlspecialchars($tc['first_name'] . ' ' . $tc['last_name']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">2. Mother's Name</span>
            <span class="detail-value text-uppercase"><?php echo htmlspecialchars($tc['mother_name']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">3. Father's / Guardian's Name</span>
            <span class="detail-value text-uppercase"><?php echo htmlspecialchars($tc['father_name']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">4. Gender</span>
            <span class="detail-value"><?php echo htmlspecialchars($tc['gender']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">5. Date of Birth (DD-MM-YYYY)</span>
            <span class="detail-value"><?php echo date('d-m-Y', strtotime($tc['date_of_birth'])); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label text-indent" style="font-size: 14px; text-indent: 20px;">(In words)</span>
            <span class="detail-value" style="font-size: 14px;">
                <?php echo date('F jS, Y', strtotime($tc['date_of_birth'])); ?> <!-- Simple representation for template -->
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">6. Date of First Admission</span>
            <span class="detail-value"><?php echo date('d-m-Y', strtotime($tc['admission_date'])); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">7. Class in which pupil last studied</span>
            <span class="detail-value">Class <?php echo htmlspecialchars($tc['current_class'] . ' (' . $tc['current_section'] . ')'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">8. Date of leaving the school</span>
            <span class="detail-value"><?php echo date('d-m-Y', strtotime($tc['date_of_leaving'])); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">9. Reason for leaving the school</span>
            <span class="detail-value"><?php echo htmlspecialchars($tc['reason_for_leaving']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">10. Address recorded at school</span>
            <span class="detail-value"><?php echo htmlspecialchars($tc['address']); ?></span>
        </div>
    </div>
    
    <div style="margin-top: 40px;">
        <p>This is to certify that the above information is in accordance with the school registers and records. The student has cleared all dues toward the school library, laboratories, and accounts.</p>
    </div>
    
    <div class="d-flex justify-content-between mb-0 mt-5 pt-3">
        <div><strong>Date of Issue:</strong> <?php echo date('d-m-Y', strtotime($tc['issue_date'])); ?></div>
        <div class="small text-muted">Generated By: <?php echo htmlspecialchars($tc['issuer_name']); ?></div>
    </div>
    
    <div class="signatures">
        <div class="sig-block">
            <div class="sig-line">Prepared By (Clerk)</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Checked By (Head Clerk)</div>
        </div>
        <div class="sig-block">
            <div style="width: 80px; height: 80px; border: 2px dashed #ccc; border-radius: 50%; margin: -40px auto 10px auto; line-height: 80px; color: #ccc;" class="small">School Seal</div>
            <div class="sig-line border-0 p-0 text-center"><strong>Signature of Principal</strong></div>
            <div class="small">Global Standard High School</div>
        </div>
    </div>
    
</div>

</body>
</html>
