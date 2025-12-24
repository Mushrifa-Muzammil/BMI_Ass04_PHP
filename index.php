<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for potential future use
session_start();

// Set Sri Lankan timezone (UTC+5:30)
date_default_timezone_set('Asia/Colombo');

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("HTTP/1.1 405 Method Not Allowed");
    die("<h1>405 Method Not Allowed</h1><p>Please submit the form from the calculator page.</p>");
}

// Initialize variables and error messages
$errors = [];
$data = [
    'name' => '',
    'age' => '',
    'address' => '',
    'contact' => '',
    'weight' => '',
    'height' => ''
];

// Input validation function with enhanced security
function validate_input($input, $type = 'text') {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    switch($type) {
        case 'number':
            if (!is_numeric($input)) return false;
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'int':
            if (!is_numeric($input)) return false;
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'phone':
            $input = preg_replace('/[^\d\s\-\+\(\)]/', '', $input);
            return $input;
        default:
            return $input;
    }
}

// Validate and sanitize all inputs
$data['name'] = validate_input($_POST['name'] ?? '');
if (empty($data['name']) || !preg_match('/^[a-zA-Z\s]{2,50}$/', $data['name'])) {
    $errors['name'] = "Valid name is required (2-50 letters and spaces only)";
}

$data['age'] = validate_input($_POST['age'] ?? '', 'int');
if (empty($data['age']) || $data['age'] < 1 || $data['age'] > 120) {
    $errors['age'] = "Valid age is required (1-120)";
}

$data['address'] = validate_input($_POST['address'] ?? '');
if (empty($data['address']) || strlen($data['address']) < 5) {
    $errors['address'] = "Valid address is required (minimum 5 characters)";
}

$data['contact'] = validate_input($_POST['contact'] ?? '', 'phone');
if (empty($data['contact']) || !preg_match('/^[\d\s\-\+\(\)]{10,20}$/', $data['contact'])) {
    $errors['contact'] = "Valid contact number is required";
}

// REQUIRED: Weight validation
if (empty($_POST['weight'])) {
    $errors['weight'] = "Weight is required";
} else {
    $data['weight'] = validate_input($_POST['weight'], 'number');
    if (!is_numeric($data['weight']) || $data['weight'] <= 0 || $data['weight'] > 300) {
        $errors['weight'] = "Valid weight is required (0.1-300 kg)";
    }
}

// REQUIRED: Height validation
if (empty($_POST['height'])) {
    $errors['height'] = "Height is required";
} else {
    $data['height'] = validate_input($_POST['height'], 'number');
    if (!is_numeric($data['height']) || $data['height'] <= 0 || $data['height'] > 250) {
        $errors['height'] = "Valid height is required (50-250 cm)";
    }
}

// Check for errors
if (!empty($errors)) {
    http_response_code(400);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Validation Error</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        <div class="container" style="max-width: 800px;">
            <header style="background: linear-gradient(to right, #e74c3c, #c0392b);">
                <h1><i class="fas fa-exclamation-triangle"></i> Validation Error</h1>
                <p>Please correct the following errors in the form</p>
            </header>
            
            <div class="main-content" style="grid-template-columns: 1fr; padding: 30px;">
                <div class="form-container">
                    <h2 style="color: #e74c3c; margin-bottom: 20px;">Form Errors:</h2>
                    <ul style="list-style-type: none; padding-left: 0;">
                        <?php foreach ($errors as $field => $error): ?>
                            <li style="padding: 10px; margin-bottom: 10px; background: #ffeaea; border-left: 4px solid #e74c3c; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-times-circle" style="color: #e74c3c;"></i>
                                <strong style="text-transform: capitalize;"><?php echo htmlspecialchars($field); ?>:</strong> 
                                <?php echo htmlspecialchars($error); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div style="margin-top: 30px; text-align: center;">
                        <a href="index.html" class="btn submit-btn" style="text-decoration: none; display: inline-block; width: auto; padding: 12px 30px;">
                            <i class="fas fa-arrow-left"></i> Back to Calculator
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Calculate BMI
$weight_kg = floatval($data['weight']);
$height_cm = floatval($data['height']);
$height_m = $height_cm / 100;

// BMI formula: weight (kg) / [height (m)]²
$bmi = $weight_kg / ($height_m * $height_m);
$bmi = round($bmi, 1);

// Additional calculations
$weight_pounds = round($weight_kg * 2.20462, 1);
$height_inches = round($height_cm / 2.54, 1);
$feet = floor($height_inches / 12);
$inches = round($height_inches % 12);

// Determine BMI category
if ($bmi < 16) {
    $category = "Severely Underweight";
    $category_class = "underweight";
    $advice = "You should consult a healthcare provider for dietary guidance.";
} elseif ($bmi < 18.5) {
    $category = "Underweight";
    $category_class = "underweight";
    $advice = "Consider increasing calorie intake with nutritious foods.";
} elseif ($bmi < 25) {
    $category = "Normal (Healthy Weight)";
    $category_class = "normal";
    $advice = "Great! Maintain your current lifestyle with balanced nutrition.";
} elseif ($bmi < 30) {
    $category = "Overweight";
    $category_class = "overweight";
    $advice = "Consider moderate exercise and balanced diet to maintain healthy weight.";
} elseif ($bmi < 35) {
    $category = "Obese Class I";
    $category_class = "obese";
    $advice = "Regular exercise and dietary changes are recommended. Consult a doctor.";
} elseif ($bmi < 40) {
    $category = "Obese Class II";
    $category_class = "obese";
    $advice = "Health risks increase. Professional medical advice is strongly recommended.";
} else {
    $category = "Obese Class III";
    $category_class = "obese";
    $advice = "Immediate medical consultation is advised to manage health risks.";
}

// Format Sri Lankan time
$sri_lankan_time = date('Y-m-d H:i:s');
$formatted_date = date('F j, Y'); // e.g., December 24, 2023
$formatted_time = date('g:i a'); // e.g., 2:30 pm
$day_of_week = date('l'); // e.g., Sunday

// Store data in session for potential future use
$_SESSION['bmi_data'] = [
    'name' => $data['name'],
    'bmi' => $bmi,
    'category' => $category,
    'timestamp' => $sri_lankan_time,
    'location' => 'Sri Lanka (IST)'
];

// Generate the report
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Report - <?php echo htmlspecialchars($data['name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-file-medical-alt"></i> BMI Report</h1>
            <p>
                <i class="fas fa-clock"></i> Generated on: <?php echo $day_of_week; ?>, <?php echo $formatted_date; ?> at <?php echo $formatted_time; ?> 
                <span style="background: rgba(255,255,255,0.2); padding: 3px 8px; border-radius: 4px; margin-left: 10px;">
                    <i class="fas fa-globe-asia"></i> <!--Sri lanka mention-->(IST)
                </span>
            </p>
        </header>

        <div class="main-content" style="grid-template-columns: 1fr;">
            <div class="form-container">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h2 style="color: #2c3e50;">Report for <?php echo htmlspecialchars($data['name']); ?></h2>
                    <p style="color: #666;">Personal Health Assessment</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="info-card">
                        <h3><i class="fas fa-user-circle"></i> Personal Details</h3>
                        <p><strong>Age:</strong> <?php echo htmlspecialchars($data['age']); ?> years</p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($data['address']); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($data['contact']); ?></p>
                        <p><strong>Report Date:</strong> <?php echo $formatted_date; ?></p>
                        <p><strong>Report Time:</strong> <?php echo $formatted_time; ?> (IST)</p>
                    </div>

                    <div class="info-card">
                        <h3><i class="fas fa-weight"></i> Measurement Details</h3>
                        <p><strong>Weight:</strong> <?php echo htmlspecialchars($weight_kg); ?> kg (<?php echo $weight_pounds; ?> lbs)</p>
                        <p><strong>Height:</strong> <?php echo htmlspecialchars($height_cm); ?> cm (<?php echo $feet; ?>'<?php echo $inches; ?>")</p>
                        <p><strong>BMI Formula:</strong> Weight(kg) / Height(m)²</p>
                        <p><strong>Time Zone:</strong> Asia/Colombo (UTC+5:30)</p>
                    </div>
                </div>

                <!-- BMI Result Card -->
                <div class="bmi-result-card <?php echo $category_class; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <h2 style="color: white; margin: 0;">Your BMI Result</h2>
                            <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0 0;">Body Mass Index</p>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); padding: 15px 25px; border-radius: 10px; text-align: center;">
                            <div style="font-size: 3rem; font-weight: bold; color: white;"><?php echo $bmi; ?></div>
                        </div>
                    </div>
                    
                    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <h3 style="color: white; margin: 0 0 10px 0;">Category: <?php echo $category; ?></h3>
                        <p style="color: rgba(255,255,255,0.9); margin: 0;"><?php echo $advice; ?></p>
                    </div>
                </div>

                <!-- BMI Scale Visualization -->
                <div style="margin: 30px 0;">
                    <h3><i class="fas fa-chart-bar"></i> BMI Scale</h3>
                    <div style="display: flex; height: 40px; border-radius: 8px; overflow: hidden; margin: 15px 0;">
                        <div style="flex: 16; background: #3498db;" title="Underweight (<16)"></div>
                        <div style="flex: 2.5; background: #2ecc71;" title="Underweight (16-18.5)"></div>
                        <div style="flex: 6.5; background: #2ecc71;" title="Normal (18.5-25)"></div>
                        <div style="flex: 5; background: #f39c12;" title="Overweight (25-30)"></div>
                        <div style="flex: 5; background: #e74c3c;" title="Obese I (30-35)"></div>
                        <div style="flex: 5; background: #c0392b;" title="Obese II (35-40)"></div>
                        <div style="flex: 5; background: #a93226;" title="Obese III (40+)"></div>
                    </div>
                    
                    <!-- BMI Indicator -->
                    <div style="position: relative; height: 40px;">
                        <?php
                        $bmi_position = ($bmi < 16) ? 0 : 
                                       (($bmi < 18.5) ? ($bmi - 16) / 2.5 * 16 : 
                                       (($bmi < 25) ? 16 + (($bmi - 18.5) / 6.5 * 2.5) : 
                                       (($bmi < 30) ? 18.5 + (($bmi - 25) / 5 * 6.5) : 
                                       (($bmi < 35) ? 23.5 + (($bmi - 30) / 5 * 5) : 
                                       (($bmi < 40) ? 28.5 + (($bmi - 35) / 5 * 5) : 
                                       33.5 + (($bmi - 40) / 10 * 5))))));
                        ?>
                        <div style="position: absolute; left: <?php echo min(95, $bmi_position); ?>%; transform: translateX(-50%); top: 0;">
                            <div style="width: 0; height: 0; border-left: 10px solid transparent; border-right: 10px solid transparent; border-top: 15px solid #2c3e50;"></div>
                            <div style="background: #2c3e50; color: white; padding: 5px 10px; border-radius: 5px; font-weight: bold; margin-top: 2px;">
                                <?php echo $bmi; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 30px 0;">
                    <h3><i class="fas fa-heartbeat"></i> Health Recommendations</h3>
                    <ul style="padding-left: 20px; margin-top: 10px;">
                        <li>Maintain a balanced diet rich in fruits, vegetables, and whole grains</li>
                        <li>Engage in at least 150 minutes of moderate exercise per week</li>
                        <li>Stay hydrated by drinking 8-10 glasses of water daily</li>
                        <li>Monitor your weight regularly and track changes</li>
                        <li>Consult with a healthcare professional for personalized advice</li>
                    </ul>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="index.html" class="btn submit-btn" style="text-decoration: none; display: inline-block; width: auto; padding: 12px 30px; margin-right: 10px;">
                        <i class="fas fa-calculator"></i> Calculate Another BMI
                    </a>
                    <button onclick="window.print()" class="btn reset-btn" style="width: auto; padding: 12px 30px;">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>

                <div style="margin-top: 20px; font-size: 0.85rem; color: #666; text-align: center; padding: 15px; border-top: 1px solid #eee;">
                    <p><strong>Disclaimer:</strong> This BMI calculator is for informational purposes only. It is not a substitute for professional medical advice, diagnosis, or treatment.</p>
                    <p><strong>Report Generated:</strong> <?php echo $sri_lankan_time; ?> (Sri Lanka Time - IST)</p>
                </div>
            </div>
        </div>

        <footer>
            <p>
                BMI Calculator &copy; <?php echo date('Y'); ?> | 
                Report generated on <?php echo $formatted_date; ?> at <?php echo $formatted_time; ?> (IST) | 
                For informational purposes only
            </p>
        </footer>
    </div>

    <style>
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e1e1e1;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .info-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-card p {
            margin: 8px 0;
            color: #555;
        }
        
        .bmi-result-card {
            padding: 25px;
            border-radius: 15px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .bmi-result-card.underweight {
            background: linear-gradient(to right, #3498db, #2980b9);
        }
        
        .bmi-result-card.normal {
            background: linear-gradient(to right, #2ecc71, #27ae60);
        }
        
        .bmi-result-card.overweight {
            background: linear-gradient(to right, #f39c12, #d68910);
        }
        
        .bmi-result-card.obese {
            background: linear-gradient(to right, #e74c3c, #c0392b);
        }
        
        @media print {
            .container {
                box-shadow: none;
            }
            footer, .btn {
                display: none;
            }
        }
    </style>
</body>
</html>