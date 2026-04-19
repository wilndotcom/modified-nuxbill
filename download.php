<?php
include 'config.php';
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Function to get a setting value
function getSettingValue($mysqli, $setting)
{
    $query = $mysqli->prepare("SELECT value FROM tbl_appconfig WHERE setting = ?");
    $query->bind_param("s", $setting);
    $query->execute();
    $result = $query->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['value'];
    }
    return '';
}

// Fetch hotspot title and description from tbl_appconfig
$hotspotTitle = getSettingValue($mysqli, 'hotspot_title');
$description = getSettingValue($mysqli, 'description');
$phone = getSettingValue($mysqli, 'phone');
$company = getSettingValue($mysqli, 'CompanyName');
$color_scheme = getSettingValue($mysqli, 'color_scheme');
$shape = getSettingValue($mysqli, 'shape_selector');
$auto_manual_display = getSettingValue($mysqli, 'auto_manual_display');


if ($shape == 'square') {
    $shape_card_class_name = 'w-64 h-64 rounded-lg';
} elseif ($shape == 'rectangle') {
    $shape_card_class_name = 'w-80 h-48 rounded-lg';
} elseif ($shape == 'circle') {
    $shape_card_class_name = 'w-64 h-64 rounded-full';
} elseif ($shape == 'oval') {
    $shape_card_class_name = 'w-80 h-48 rounded-full';
} else {
    $shape_card_class_name = 'rounded-lg';
}

// Fetch router name and router ID from tbl_appconfig
$routerName = getSettingValue($mysqli, 'router_name');
$routerId = getSettingValue($mysqli, 'router_id');

// Fetch available plans
$planQuery = "SELECT id, name_plan, price, validity, validity_unit FROM tbl_plans WHERE routers = ? AND type = 'Hotspot'";
$planStmt = $mysqli->prepare($planQuery);
$planStmt->bind_param("s", $routerName);
$planStmt->execute();
$planResult = $planStmt->get_result();

// Initialize HTML content variable
$htmlContent = "<!DOCTYPE html>\n";
$htmlContent .= "<html lang=\"en\">\n";
$htmlContent .= "<head>\n";
$htmlContent .= "    <meta charset=\"UTF-8\">\n";
$htmlContent .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
$htmlContent .= "<meta data-react-helmet=\"true\" name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover\">\n";
$htmlContent .= "    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">\n";
$htmlContent .= "    <meta name=\"description\" content=\"" . htmlspecialchars($description) . "\">\n";
$htmlContent .= "    <title>" . htmlspecialchars($hotspotTitle) . " Hotspot Template - Index</title>\n";
$htmlContent .= "    <script src=\"https://cdn.tailwindcss.com\"></script>\n";
$htmlContent .= "    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css\">\n";
$htmlContent .= "    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/glider-js@1.7.7/glider.min.css\" />\n";
$htmlContent .= "    <script src=\"https://cdn.jsdelivr.net/npm/glider-js@1.7.7/glider.min.js\"></script>\n";
$htmlContent .= "    <link rel=\"preconnect\" href=\"https://cdn.jsdelivr.net\">\n";
$htmlContent .= "    <link rel=\"preconnect\" href=\"https://cdnjs.cloudflare.com\" crossorigin>\n";
$htmlContent .= "    <link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\">\n";
$htmlContent .= "</head>\n";


$htmlContent .= "<body class=\"font-sans antialiased text-gray-900\">\n";
$htmlContent .= "    <!-- Sticky Header -->\n";
$htmlContent .= "<header class=\"bg-gradient-to-r from-" . htmlspecialchars($color_scheme) . "-600 to-" . htmlspecialchars($color_scheme) . "-500 text-white fixed w-full z-10 shadow-lg\">\n";
$htmlContent .= "    <div class=\"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4\">\n";
$htmlContent .= "        <div class=\"flex items-center justify-between\">\n";
$htmlContent .= "            <!-- Logo and title area -->\n";
$htmlContent .= "            <div class=\"flex items-center space-x-3\">\n";
$htmlContent .= "                <i class=\"fa fa-wifi text-2xl\"></i> <!-- Icon for visual appeal -->\n";
$htmlContent .= "                <h1 class=\"text-2xl font-semibold tracking-wide\">" . htmlspecialchars($hotspotTitle) . " Hotspot Login</h1>\n";
$htmlContent .= "            </div>\n";
$htmlContent .= "        </div>\n";
$htmlContent .= "    </div>\n";
$htmlContent .= "</header>\n";


$htmlContent .= "    <!-- Main content -->\n";
$htmlContent .= "    <main class=\"pt-24\">\n";
$htmlContent .= "        <section class=\"bg-white\">\n";
$htmlContent .= "            <div class=\"max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8\">\n";
$htmlContent .= "                <h2 class=\"text-3xl font-extrabold text-gray-900 mb-6\">" . htmlspecialchars($description) . "</h2>\n";


$htmlContent .= "    <!-- Voucher Redemption Section -->\n";
$htmlContent .= "    <div class=\"mt-10 max-w-xl mx-auto\">\n";
$htmlContent .= "        <div class=\"text-center\">\n";
$htmlContent .= "            <h3 class=\"text-2xl font-extrabold text-gray-900\">REDEEM VOUCHER</h3>\n";
$htmlContent .= "            <p class=\"mt-4 text-xl text-gray-500\">Enter your voucher code to get connected.</p>\n";
$htmlContent .= "        </div>\n";
$htmlContent .= "        <form id=\"redeemVoucherForm\" class=\"mt-6\">\n";
$htmlContent .= "            <input type=\"text\" id=\"voucher_code\" name=\"voucher_code\"\n";
$htmlContent .= "                class=\"w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500\"\n";
$htmlContent .= "                placeholder=\"Enter Voucher Code\" required>\n";
$htmlContent .= "            <button type=\"button\" onclick=\"redeemVoucher(" . $routerId . ")\"\n";
$htmlContent .= "                class=\"mt-4 w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700\">\n";
$htmlContent .= "                Redeem Voucher\n";
$htmlContent .= "            </button>\n";
$htmlContent .= "        </form>\n";
$htmlContent .= "        <p id=\"message\" class=\"mt-4 text-center text-blue-500\"></p>\n";
$htmlContent .= "    </div>\n";


$htmlContent .= "                <!-- Pricing Section -->\n";
$htmlContent .= "                <div class=\"mt-10\">\n";
$htmlContent .= "                    <div class=\"text-center\">\n";
$htmlContent .= "                        <h3 class=\"text-2xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-3xl sm:leading-9\">\n";
$htmlContent .= "                            CHECK OUR PRICING\n";
$htmlContent .= "                        </h3>\n";
$htmlContent .= "                        <p class=\"mt-4 max-w-2xl text-xl leading-7 text-gray-500 lg:mx-auto\">\n";
$htmlContent .= "                            Choose the plan that fits your needs.\n";
$htmlContent .= "                        </p>\n";
$htmlContent .= "                    </div>\n";
$htmlContent .= "                </div>\n";
$htmlContent .= "            </div>\n";
$htmlContent .= "        </section>\n";
$htmlContent .= "    </main>\n";



$htmlContent .= "<div id=\"plansContainer\" class=\"mt-10 max-w-7xl mx-auto grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-5\">\n";


if ($auto_manual_display == 'manual') {
    while ($plan = $planResult->fetch_assoc()) {
        $htmlContent .= "    <div onclick=\"handlePhoneNumberSubmission(event, this.getAttribute('data-plan-id'), this.getAttribute('data-router-id'));\" 
        data-plan-id=\"" . htmlspecialchars($plan['id']) . "\" 
        data-router-id=\"" . htmlspecialchars($routerId) . "\" 
        class=\"flex flex-col " . htmlspecialchars($shape_card_class_name) . " shadow-xl overflow-hidden transform transition duration-500 hover:scale-105\">\n";    
        $htmlContent .= "        <div class=\"px-4 py-5 bg-gradient-to-tr from-" . htmlspecialchars($color_scheme) . "-50 to-" . htmlspecialchars($color_scheme) . "-200 text-center\">\n";
        $htmlContent .= "            <span class=\"inline-flex px-3 py-1 rounded-full text-xs font-semibold tracking-wide uppercase bg-" . htmlspecialchars($color_scheme) . "-800 text-" . htmlspecialchars($color_scheme) . "-50\">\n";
        $htmlContent .=                  htmlspecialchars($plan['name_plan']) . "\n";
        $htmlContent .= "            </span>\n";
        $htmlContent .= "            <div class=\"mt-4 text-4xl leading-none font-extrabold text-" . htmlspecialchars($color_scheme) . "-800\">\n";
        $htmlContent .= "                <span class=\"text-lg font-medium text-" . htmlspecialchars($color_scheme) . "-600\">ksh</span>\n";
        $htmlContent .=                  htmlspecialchars($plan['price']) . "\n";
        $htmlContent .= "            </div>\n";
        $htmlContent .= "            <p class=\"mt-2 text-md leading-5 text-" . htmlspecialchars($color_scheme) . "-700 text-center\">\n";
        $htmlContent .=                  htmlspecialchars($plan['validity']) . " " . htmlspecialchars($plan['validity_unit']) . " Unlimited\n";
        $htmlContent .= "            </p>\n";
        $htmlContent .= "        </div>\n";
        $htmlContent .= "        <div class=\"px-4 pt-4 pb-6 bg-" . htmlspecialchars($color_scheme) . "-500 text-center\">\n";
        $htmlContent .= "            <a href=\"#\" class=\"inline-block text-" . htmlspecialchars($color_scheme) . "-800 bg-" . htmlspecialchars($color_scheme) . "-50 hover:bg-" . htmlspecialchars($color_scheme) . "-100 focus:outline-none focus:ring-4 focus:ring-pink-500 focus:ring-opacity-50 transform transition duration-150 ease-in-out rounded-lg font-semibold px-3 py-2 text-xs shadow-lg cursor-pointer\">\n";
        $htmlContent .= "                Click Here To Connect\n";
        $htmlContent .= "            </a>\n";
        $htmlContent .= "        </div>\n";
        $htmlContent .= "    </div>\n";
    }
}


$htmlContent .= "</div>\n";


$htmlContent .= "<div class=\"container mx-auto px-4\">\n";
$htmlContent .= "    <div class=\"max-w-md mx-auto bg-white rounded-lg overflow-hidden md:max-w-lg\">\n";

$htmlContent .= "    <!-- Mpesa Redemption Section -->\n";
$htmlContent .= "    <div class=\"mt-10 max-w-xl mx-auto\">\n";
$htmlContent .= "        <div class=\"text-center\">\n";
$htmlContent .= "            <h3 class=\"text-2xl font-extrabold text-gray-900\">LOGIN WITH MPESA CODE / MPESA MESSAGE</h3>\n";
$htmlContent .= "            <p class=\"mt-4 text-xl text-gray-500\">Enter your MPESA code to get connected.</p>\n";
$htmlContent .= "        </div>\n";
$htmlContent .= "        <form id=\"redeemMpesaForm\" class=\"mt-6\">\n";
$htmlContent .= "            <input type=\"text\" id=\"mpesa_code\" name=\"mpesa_code\"\n";
$htmlContent .= "                class=\"w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500\"\n";
$htmlContent .= "                placeholder=\"Enter MPESA Code\" required>\n";
$htmlContent .= "            <button type=\"button\" onclick=\"redeemMpesa()\"\n";
$htmlContent .= "                class=\"mt-4 w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700\">\n";
$htmlContent .= "                Connect with MPESA Code / MPESA Message\n";
$htmlContent .= "            </button>\n";
$htmlContent .= "        </form>\n";
$htmlContent .= "        <p id=\"mpesaMessage\" class=\"mt-4 text-center text-blue-500\"></p>\n";
$htmlContent .= "    </div>\n";



$htmlContent .= "        <div class=\"md:flex\">\n";
$htmlContent .= "            <div class=\"w-full p-5\">\n";
$htmlContent .= "                <div class=\"text-center\">\n";
$htmlContent .= "                    <h3 class=\"text-2xl text-gray-900\">Already Have an Active Package?</h3>\n";
$htmlContent .= "                </div>\n";

$htmlContent .= "                <form id=\"loginForm\" class=\"form\" name=\"login\" action=\"$(link-login-only)\" method=\"post\" $(if chap-id)onSubmit=\"return doLogin()\"$(endif)>\n";
$htmlContent .= "                    <input type=\"hidden\" name=\"dst\" value=\"$(link-orig)\" />\n";
$htmlContent .= "                    <input type=\"hidden\" name=\"popup\" value=\"true\" />\n";
$htmlContent .= "                    <div class=\"mb-4\">\n";
$htmlContent .= "                        <label class=\"block text-gray-700 text-sm font-bold mb-2\" for=\"username\">Username</label>\n";
$htmlContent .= "                        <input id=\"usernameInput\" class=\"shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline\" name=\"username\" type=\"text\" value=\"\" placeholder=\"Username\">\n";
$htmlContent .= "                    </div>\n";
$htmlContent .= "                    <div class=\"mb-6\">\n";
$htmlContent .= "                        <label class=\"block text-gray-700 text-sm font-bold mb-2\" for=\"password\">Password</label>\n";
$htmlContent .= "                        <input id=\"passwordInput\" class=\"shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline\" name=\"password\" type=\"password\" placeholder=\"******************\">\n";
$htmlContent .= "                    </div>\n";
$htmlContent .= "                    <div class=\"flex items-center justify-between\">\n";
$htmlContent .= "                        <button id=\"submitBtn\" class=\"bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline\" type=\"button\">\n";
$htmlContent .= "                            Click Here To Connect\n";
$htmlContent .= "                        </button>\n";
$htmlContent .= "                    </div>\n";
$htmlContent .= "                </form>\n";
$htmlContent .= "            </div>\n";
$htmlContent .= "        </div>\n";
$htmlContent .= "    </div>\n";
$htmlContent .= "</div>\n";


$htmlContent .= "<script>\n";
$htmlContent .= "    // Create an auto login with a timeout of 2 minutes\n";
$htmlContent .= "    function autoLogin() {\n";
$htmlContent .= "        // Get existing account number\n";
$htmlContent .= "        let accountNumber = getAccountNumberFromCookie();\n\n";

$htmlContent .= "        // Check if empty\n";
$htmlContent .= "        if (!accountNumber) {\n";
$htmlContent .= "            // Populate username input\n";
$htmlContent .= "            document.getElementById('usernameInput').value = accountNumber;\n";
$htmlContent .= "            // Populate password input\n";
$htmlContent .= "            document.getElementById('passwordInput').value = '1234';\n";
$htmlContent .= "            // Add a reconnect button\n";
$htmlContent .= "            document.getElementById('submitBtn').innerHTML = 'Reconnect';\n";
$htmlContent .= "        }\n\n";

$htmlContent .= "        // Auto submit form after 2 minutes\n";
$htmlContent .= "        setTimeout(function () {\n";
$htmlContent .= "            document.getElementById('submitBtn').click();\n";
$htmlContent .= "        }, 120000);\n";
$htmlContent .= "        return true;\n";
$htmlContent .= "    }\n\n";

$htmlContent .= "    autoLogin();\n";
$htmlContent .= "</script>\n";



if ($auto_manual_display == 'auto') {
    $htmlContent .= "<script>\n";
    $htmlContent .= "async function fetchPlans() {\n";
    $htmlContent .= "    try {\n";
    $htmlContent .= "        const response = await fetch('" . APP_URL . "/index.php?_route=plugin/CreateHotspotuser&type=hotspot_plans', {\n";
    $htmlContent .= "            method: 'POST',\n";
    $htmlContent .= "            headers: { 'Content-Type': 'application/json' },\n";
    $htmlContent .= "            body: JSON.stringify({ router_id: '" . $routerId . "' })\n";
    $htmlContent .= "        });\n\n";

    $htmlContent .= "        if (!response.ok) throw new Error('Network response was not ok');\n\n";

    $htmlContent .= "        const dataPlan = await response.json();\n";
    $htmlContent .= "        if (dataPlan.status === 'error') throw new Error(dataPlan.message);\n\n";

    $htmlContent .= "        if (Array.isArray(dataPlan) && dataPlan.length > 0) {\n";

    $htmlContent .= "         const plans = dataPlan.flatMap(router => router.plans_hotspot || []);\n";

    $htmlContent .= "        const plansContainer = document.getElementById('plansContainer');\n";
    $htmlContent .= "        plansContainer.innerHTML = '';\n\n";

    $htmlContent .= "        plans.forEach(plan => {\n";
    $htmlContent .= "            const planElement = document.createElement('div');\n";
    $htmlContent .= "            planElement.className = '';\n";

    $htmlContent .= "            planElement.innerHTML = `\n";
    $htmlContent .= "                <div onclick='handlePhoneNumberSubmission(event, this.getAttribute(\"data-plan-id\"), this.getAttribute(\"data-router-id\")); return false;' \n";
    $htmlContent .= "                     data-plan-id='\${plan.planId}' data-router-id='\${plan.routerId}' \n";
    $htmlContent .= "                     class='flex flex-col \${plan.shape_card_class_name} shadow-xl overflow-hidden transform transition duration-500 hover:scale-105'>\n";
    $htmlContent .= "                    <div class='px-4 py-5 bg-gradient-to-tr from-\${plan.color_scheme}-50 to-\${plan.color_scheme}-200 text-center'>\n";
    $htmlContent .= "                        <span class='inline-flex px-3 py-1 rounded-full text-xs font-semibold tracking-wide uppercase bg-\${plan.color_scheme}-800 text-\${plan.color_scheme}-50'>\n";
    $htmlContent .= "                            \${plan.planname}\n";
    $htmlContent .= "                        </span>\n";
    $htmlContent .= "                        <div class='mt-4 text-4xl leading-none font-extrabold text-\${plan.color_scheme}-800'>\n";
    $htmlContent .= "                            <span class='text-lg font-medium text-\${plan.color_scheme}-600'>\${plan.currency}</span> \${plan.price}\n";
    $htmlContent .= "                        </div>\n";
    $htmlContent .= "                        <p class='mt-2 text-md leading-5 text-\${plan.color_scheme}-700 text-center'>\n";
    $htmlContent .= "                           \${plan.validity} \${plan.timelimit} Unlimited\n";
    $htmlContent .= "                        </p>\n";
    $htmlContent .= "                    </div>\n";
    $htmlContent .= "                    <div class='px-4 pt-4 pb-6 bg-\${plan.color_scheme}-500 text-center'>\n";
    $htmlContent .= "                        <a href='#' class='inline-block text-\${plan.color_scheme}-800 bg-\${plan.color_scheme}-50 hover:bg-\${plan.color_scheme}-100 focus:outline-none focus:ring-4 focus:ring-\${plan.color_scheme}-500 focus:ring-opacity-50 transform transition duration-150 ease-in-out rounded-lg font-semibold px-3 py-2 text-xs shadow-lg cursor-pointer'>\n";
    $htmlContent .= "                            Click Here To Connect\n";
    $htmlContent .= "                        </a>\n";
    $htmlContent .= "                    </div>\n";
    $htmlContent .= "                </div>\n";
    $htmlContent .= "            `;\n\n";

    $htmlContent .= "            plansContainer.appendChild(planElement);\n";
    $htmlContent .= "        });\n\n";

    $htmlContent .= "    } else {\n";
    $htmlContent .= "         console.error('Invalid data format or empty response:', dataPlan);\n";
    $htmlContent .= "    }\n";

    $htmlContent .= "    } catch (error) {\n";
    $htmlContent .= "        console.error('Error fetching plans:', error);\n";
    $htmlContent .= "    }\n";
    $htmlContent .= "}\n\n";

    $htmlContent .= "fetchPlans();\n";
    $htmlContent .= "</script>\n";
}


$htmlContent .= "<script>\n";
// Function to redeem a voucher
$htmlContent .= "function redeemVoucher(router_id) {\n";
$htmlContent .= "    const voucherCode = document.getElementById('voucher_code').value;\n";
$htmlContent .= "    if (!voucherCode) {\n";
$htmlContent .= "        document.getElementById('message').innerText = 'Please enter a valid voucher code.';\n";
$htmlContent .= "        return;\n";
$htmlContent .= "    }\n\n";

$htmlContent .= "    fetch('" . APP_URL . "/index.php?_route=plugin/CreateHotspotuser&type=redeem_voucher', {\n";
$htmlContent .= "        method: 'POST',\n";
$htmlContent .= "        headers: { 'Content-Type': 'application/json' },\n";
$htmlContent .= "        body: JSON.stringify({ voucher_code: voucherCode, account_number: generateAccountNumber(), router_id: router_id })\n";
$htmlContent .= "    })\n";
$htmlContent .= "    .then(response => {\n";
$htmlContent .= "        if (!response.ok) throw new Error('Network response was not ok');\n";
$htmlContent .= "        return response.json();\n";
$htmlContent .= "    })\n";
$htmlContent .= "    .then(data => {\n";
$htmlContent .= "        if (data.status === 'error') throw new Error(data.message);\n";
$htmlContent .= "        console.log('Voucher redemption data:', data);\n\n";
$htmlContent .= "        if (data && (data.status === 'success' || data.Status === 'used')) {\n";
$htmlContent .= "            document.getElementById('message').innerText = 'Voucher redeemed successfully.';\n";
$htmlContent .= "            document.getElementById('usernameInput').value = data.username;\n";
$htmlContent .= "            document.getElementById('passwordInput').value = data.tyhK;\n";
$htmlContent .= "            storeAccountNumberInCookie(data.username);\n\n";
$htmlContent .= "            document.getElementById('submitBtn').click();\n";
$htmlContent .= "        } else {\n";
$htmlContent .= "            document.getElementById('message').innerText = data?.message || 'An error occurred. Please try again.';\n";
$htmlContent .= "        }\n";
$htmlContent .= "    })\n";
$htmlContent .= "    .catch(error => {\n";
$htmlContent .= "        console.error('Error redeeming voucher:', error);\n";
$htmlContent .= "        document.getElementById('message').innerText = error.message || 'An error occurred. Please try again.';\n";
$htmlContent .= "    });\n";
$htmlContent .= "}\n\n";

// Function to redeem an MPESA code
$htmlContent .= "function redeemMpesa() {\n";
$htmlContent .= "    const mpesaCode = document.getElementById('mpesa_code').value;\n";
$htmlContent .= "    if (!mpesaCode) {\n";
$htmlContent .= "        document.getElementById('mpesaMessage').innerText = 'Please enter a valid MPESA code.';\n";
$htmlContent .= "        return;\n";
$htmlContent .= "    }\n\n";

$htmlContent .= "    fetch('" . APP_URL . "/index.php?_route=plugin/CreateHotspotuser&type=redeem_mpesa_code', {\n";
$htmlContent .= "        method: 'POST',\n";
$htmlContent .= "        headers: { 'Content-Type': 'application/json' },\n";
$htmlContent .= "        body: JSON.stringify({ mpesa_code: mpesaCode })\n";
$htmlContent .= "    })\n";
$htmlContent .= "    .then(response => {\n";
$htmlContent .= "        if (!response.ok) throw new Error('Network response was not ok');\n";
$htmlContent .= "        return response.json();\n";
$htmlContent .= "    })\n";
$htmlContent .= "    .then(data => {\n";
$htmlContent .= "        if (data.status === 'error') throw new Error(data.message);\n\n";
$htmlContent .= "        if (data && (data.status === 'success')) {\n";
$htmlContent .= "            document.getElementById('mpesaMessage').innerText = 'MPESA code redeemed successfully.';\n";
$htmlContent .= "            document.getElementById('usernameInput').value = data.username;\n";
$htmlContent .= "            document.getElementById('passwordInput').value = data.tyhK;\n";
$htmlContent .= "            storeAccountNumberInCookie(data.username);\n";
$htmlContent .= "            document.getElementById('submitBtn').click();\n";
$htmlContent .= "        } else {\n";
$htmlContent .= "            document.getElementById('mpesaMessage').innerText = data?.message || 'An error occurred. Please try again.';\n";
$htmlContent .= "        }\n";
$htmlContent .= "    })\n";
$htmlContent .= "    .catch(error => {\n";
$htmlContent .= "        console.error('Error redeeming MPESA code:', error);\n";
$htmlContent .= "        document.getElementById('mpesaMessage').innerText = error.message || 'An error occurred. Please try again.';\n";
$htmlContent .= "    });\n";
$htmlContent .= "}\n\n";


$htmlContent .= "// Function to generate a random account number with capital letters\n";
$htmlContent .= "function generateAccountNumber(length = 10) {\n";
$htmlContent .= "    const chars = \"ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789\";\n";
$htmlContent .= "    let accountNumber = \"\";\n";
$htmlContent .= "    for (let i = 0; i < length; i++) {\n";
$htmlContent .= "        accountNumber += chars.charAt(Math.floor(Math.random() * chars.length));\n";
$htmlContent .= "    }\n";
$htmlContent .= "    storeAccountNumberInCookie(accountNumber);\n";
$htmlContent .= "    return accountNumber;\n";
$htmlContent .= "}\n\n";

$htmlContent .= "// Function to store the account number in a cookie for 7 days\n";
$htmlContent .= "function storeAccountNumberInCookie(accountNumber) {\n";
$htmlContent .= "    try {\n";
$htmlContent .= "        const now = new Date();\n";
$htmlContent .= "        now.setTime(now.getTime() + 7 * 24 * 60 * 60 * 1000);\n";
$htmlContent .= "        document.cookie = `account_number=\${encodeURIComponent(accountNumber)}; expires=\${now . toUTCString()}; path=/; SameSite=Lax`;\n";
$htmlContent .= "        console.log(\"Account number stored successfully in cookie:\", accountNumber);\n";
$htmlContent .= "    } catch (error) {\n";
$htmlContent .= "        console.error(\"Error storing account number in cookie:\", error);\n";
$htmlContent .= "    }\n";
$htmlContent .= "}\n\n";

$htmlContent .= "// Function to retrieve the account number from the cookie\n";
$htmlContent .= "function getAccountNumberFromCookie() {\n";
$htmlContent .= "    try {\n";
$htmlContent .= "        const cookies = document.cookie.split(\"; \");\n";
$htmlContent .= "        for (const cookie of cookies) {\n";
$htmlContent .= "            const [name, value] = cookie.split(\"=\");\n";
$htmlContent .= "            if (name.trim() === \"account_number\") {\n";
$htmlContent .= "                return decodeURIComponent(value);\n";
$htmlContent .= "            }\n";
$htmlContent .= "        }\n";
$htmlContent .= "    } catch (error) {\n";
$htmlContent .= "        console.error(\"Error retrieving account number from cookie:\", error);\n";
$htmlContent .= "    }\n";
$htmlContent .= "    return null;\n";
$htmlContent .= "}\n\n";

$htmlContent .= "// Function to regenerate the account number\n";
$htmlContent .= "function regenerateAccountNumber() {\n";
$htmlContent .= "    document.cookie = \"account_number=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/\";\n";
$htmlContent .= "    const newAccountNumber = generateAccountNumber();\n";
$htmlContent .= "    storeAccountNumberInCookie(newAccountNumber);\n";
$htmlContent .= "    console.log(\"Regenerated Account Number:\", newAccountNumber);\n";
$htmlContent .= "    return newAccountNumber;\n";
$htmlContent .= "}\n\n";


$htmlContent .= "document.addEventListener('DOMContentLoaded', function () {\n";
$htmlContent .= "    function autofillLogin() {\n";
$htmlContent .= "        var username = getAccountNumberFromCookie();\n";
$htmlContent .= "        var password = '1234';\n\n";

$htmlContent .= "        if (!username) {\n";
$htmlContent .= "            console.log('No account number found in cookie. Generating a new one...');\n";
$htmlContent .= "        }\n\n";

$htmlContent .= "        var usernameInput = document.querySelector('input[name=\"username\"]');\n";
$htmlContent .= "        var passwordInput = document.querySelector('input[name=\"password\"]');\n";
$htmlContent .= "        var submitButton = document.getElementById('submitBtn');\n\n";

$htmlContent .= "        if (usernameInput && passwordInput && submitButton) {\n";
$htmlContent .= "            usernameInput.value = username;\n";
$htmlContent .= "            passwordInput.value = password;\n";
$htmlContent .= "            submitButton.click();\n";
$htmlContent .= "        }\n";
$htmlContent .= "    }\n";
$htmlContent .= "    autofillLogin();\n";
$htmlContent .= "});\n";
$htmlContent .= "</script>\n";




$htmlContent .= "<script>\n";
$htmlContent .= "function toggleFAQ(faqId) {\n";
$htmlContent .= "    var element = document.getElementById(faqId);\n";
$htmlContent .= "    if (element.style.display === \"block\") {\n";
$htmlContent .= "        element.style.display = \"none\";\n";
$htmlContent .= "    } else {\n";
$htmlContent .= "        element.style.display = \"block\";\n";
$htmlContent .= "    }\n";
$htmlContent .= "}\n";
$htmlContent .= "</script>\n";

$htmlContent .= "</section>\n";
$htmlContent .= "</main>\n";

$htmlContent .= "<!-- Footer -->\n";
$htmlContent .= "<footer class=\"bg-blue-900 text-white\">\n";
$htmlContent .= "    <div class=\"max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8\">\n";
$htmlContent .= "        <div class=\"lg:grid lg:grid-cols-3 lg:gap-8\">\n";
$htmlContent .= "            <div class=\"lg:col-span-1\">\n";
$htmlContent .= "                <h2 class=\"text-sm font-semibold uppercase tracking-wider\">\n";
$htmlContent .= "                    Contact Us\n";
$htmlContent .= "                </h2>\n";
$htmlContent .= "                <ul class=\"mt-4 space-y-4\">\n";
$htmlContent .= "                    <li>\n";
$htmlContent .= "                        <span class=\"block\">Address</span>\n";
$htmlContent .= "                    </li>\n";
$htmlContent .= "                    <li>\n";
$htmlContent .= "                        <span class=\"block\">Email: contact@" . htmlspecialchars($company) . "</span>\n";
$htmlContent .= "                    </li>\n";
$htmlContent .= "                    <li>\n";
$htmlContent .= "                        <span class=\"block\">Phone: " . htmlspecialchars($phone) . "</span>\n";
$htmlContent .= "                    </li>\n";
$htmlContent .= "                </ul>\n";
$htmlContent .= "            </div>\n";

$htmlContent .= "            <div class=\"lg:col-span-1\">\n";
$htmlContent .= "                <h2 class=\"text-sm font-semibold uppercase tracking-wider\">\n";
$htmlContent .= "                    Quick Links\n";
$htmlContent .= "                </h2>\n";
$htmlContent .= "                <ul class=\"mt-4 space-y-4\">\n";
$htmlContent .= "                    <li><a href=\"#\" class=\"hover:underline\">About Us</a></li>\n";
$htmlContent .= "                    <li><a href=\"#\" class=\"hover:underline\">Our Services</a></li>\n";
$htmlContent .= "                    <li><a href=\"#\" class=\"hover:underline\">FAQ</a></li>\n";
$htmlContent .= "                    <li><a href=\"#\" class=\"hover:underline\">Support</a></li>\n";
$htmlContent .= "                </ul>\n";
$htmlContent .= "            </div>\n";

$htmlContent .= "            <div class=\"lg:col-span-1\">\n";
$htmlContent .= "                <h2 class=\"text-sm font-semibold uppercase tracking-wider\">\n";
$htmlContent .= "                     Follow Us\n";
$htmlContent .= "                </h2>\n";
$htmlContent .= "                <div class=\"mt-4 space-x-4\">\n";
$htmlContent .= "                    <a href=\"#\" class=\"hover:text-gray-400\"><i class=\"fab fa-facebook-f\"></i></a>\n";
$htmlContent .= "                    <a href=\"#\" class=\"hover:text-gray-400\"><i class=\"fab fa-twitter\"></i></a>\n";
$htmlContent .= "                    <a href=\"#\" class=\"hover:text-gray-400\"><i class=\"fab fa-instagram\"></i></a>\n";
$htmlContent .= "                    <a href=\"#\" class=\"hover:text-gray-400\"><i class=\"fab fa-linkedin-in\"></i></a>\n";
$htmlContent .= "                </div>\n";
$htmlContent .= "            </div>\n";
$htmlContent .= "        </div>\n";

$htmlContent .= "        <div class=\"mt-8 border-t border-gray-700 pt-8 md:flex md:items-center md:justify-between\">\n";
$htmlContent .= "            <div class=\"flex space-x-6 md:order-2\">\n";
$htmlContent .= "                <a href=\"#\" class=\"text-gray-400 hover:text-gray-300\"><span class=\"sr-only\">Facebook</span><i class=\"fab fa-facebook-f\"></i></a>\n";
$htmlContent .= "                <a href=\"#\" class=\"text-gray-400 hover:text-gray-300\"><span class=\"sr-only\">Instagram</span><i class=\"fab fa-instagram\"></i></a>\n";
$htmlContent .= "                <a href=\"#\" class=\"text-gray-400 hover:text-gray-300\"><span class=\"sr-only\">Twitter</span><i class=\"fab fa-twitter\"></i></a>\n";
$htmlContent .= "                <a href=\"#\" class=\"text-gray-400 hover:text-gray-300\"><span class=\"sr-only\">LinkedIn</span><i class=\"fab fa-linkedin-in\"></i></a>\n";
$htmlContent .= "            </div>\n";
$htmlContent .= "<p class=\"mt-8 text-base leading-6 text-gray-400 md:mt-0 md:order-1\">\n";
$htmlContent .= "                &copy; 2024 " . htmlspecialchars($company) . " All rights reserved.\n";
$htmlContent .= "            </p>\n";
$htmlContent .= "        </div>\n";
$htmlContent .= "    </div>\n";
$htmlContent .= "</footer>\n";


$htmlContent .= "<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script>\n";

$htmlContent .= "<script>\n";
$htmlContent .= "    function formatPhoneNumber(phoneNumber) {\n";
$htmlContent .= "        if (phoneNumber.startsWith('+')) {\n";
$htmlContent .= "            phoneNumber = phoneNumber.substring(1);\n";
$htmlContent .= "        }\n";
$htmlContent .= "        if (phoneNumber.startsWith('0')) {\n";
$htmlContent .= "            phoneNumber = '254' + phoneNumber.substring(1);\n";
$htmlContent .= "        }\n";
$htmlContent .= "        if (phoneNumber.match(/^(7|1)/)) {\n";
$htmlContent .= "            phoneNumber = '254' + phoneNumber;\n";
$htmlContent .= "        }\n";
$htmlContent .= "        return phoneNumber;\n";
$htmlContent .= "    }\n";
$htmlContent .= "\n";

$htmlContent .= "if (!window.fetch) {\n";
$htmlContent .= "    console.error('Fetch API not supported in this browser.');\n";
$htmlContent .= "    var script = document.createElement('script');\n";
$htmlContent .= "    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/fetch/3.6.2/fetch.min.js';\n";
$htmlContent .= "    script.onload = function () {\n";
$htmlContent .= "        console.log('Fetch polyfill loaded.');\n";
$htmlContent .= "    };\n";
$htmlContent .= "    document.head.appendChild(script);\n";
$htmlContent .= "}\n\n";


$htmlContent .= "if (!window.Promise || !window.Symbol) {\n";
$htmlContent .= "    console.error('Promise or Symbol not supported in this browser.');\n";
$htmlContent .= "    var script = document.createElement('script');\n";
$htmlContent .= "    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/core-js/3.30.0/minified.js';\n";
$htmlContent .= "    script.onload = function () {\n";
$htmlContent .= "        console.log('Core-js polyfill loaded.');\n";
$htmlContent .= "    };\n";
$htmlContent .= "    document.head.appendChild(script);\n";
$htmlContent .= "}\n\n";



$htmlContent .= "async function handlePhoneNumberSubmission(event, planId, routerId) {\n";
$htmlContent .= "    event.preventDefault();\n";
$htmlContent .= "    event.stopPropagation();\n\n";
$htmlContent .= "    Swal.fire({\n";
$htmlContent .= "        title: 'Enter Your Phone Number',\n";
$htmlContent .= "        input: 'text',\n";
$htmlContent .= "        inputPlaceholder: 'Your phone number here',\n";
$htmlContent .= "        inputAttributes: { autocapitalize: 'off' },\n";
$htmlContent .= "        showCancelButton: true,\n";
$htmlContent .= "        confirmButtonText: 'Submit',\n";
$htmlContent .= "        confirmButtonColor: '#3085d6',\n";
$htmlContent .= "        cancelButtonColor: '#d33',\n";
$htmlContent .= "        showLoaderOnConfirm: true,\n";
$htmlContent .= "        preConfirm: async (phoneNumber) => {\n";
$htmlContent .= "            try {\n";
$htmlContent .= "                const formattedPhoneNumber = formatPhoneNumber(phoneNumber);\n";
$htmlContent .= "                console.log('Phone number for autofill:', formattedPhoneNumber);\n\n";

$htmlContent .= "                let accountNumber = getAccountNumberFromCookie();\n";
$htmlContent .= "                if (!accountNumber) {\n";
$htmlContent .= "                    accountNumber = generateAccountNumber();\n";
$htmlContent .= "                }\n";

$htmlContent .= "                document.getElementById('usernameInput').value = accountNumber;\n";
$htmlContent .= "                console.log('Generated Account Number:', accountNumber);\n\n";

$htmlContent .= "                const response = await fetch('" . APP_URL . "/index.php?_route=plugin/CreateHotspotuser&type=grant', {\n";
$htmlContent .= "                    method: 'POST',\n";
$htmlContent .= "                    headers: { 'Content-Type': 'application/json' },\n";
$htmlContent .= "                    body: JSON.stringify({\n";
$htmlContent .= "                        phone_number: formattedPhoneNumber,\n";
$htmlContent .= "                        plan_id: planId,\n";
$htmlContent .= "                        router_id: routerId,\n";
$htmlContent .= "                        account_number: accountNumber\n";
$htmlContent .= "                    })\n";
$htmlContent .= "                });\n\n";

$htmlContent .= "                if (!response.ok) throw new Error('Network response was not ok');\n";
$htmlContent .= "                const data = await response.json();\n";
$htmlContent .= "                if (data.status === 'error') throw new Error(data.message);\n\n";

$htmlContent .= "                Swal.fire({\n";
$htmlContent .= "                    title: 'Verifying Payment...',\n";
$htmlContent .= "                    showCancelButton: true,\n";
$htmlContent .= "                    cancelButtonText: 'Cancel Verification',\n";
$htmlContent .= "                    didOpen: () => {\n";
$htmlContent .= "                        Swal.showLoading();\n";
$htmlContent .= "                        FetchAjax(accountNumber, formattedPhoneNumber);\n";
$htmlContent .= "                    }\n";
$htmlContent .= "                }).then((result) => {\n";
$htmlContent .= "                    if (result.dismiss === Swal.DismissReason.timer) {\n";
$htmlContent .= "                        console.log('Auto-submitting after timer ends');\n";
$htmlContent .= "                        document.getElementById('submitBtn').click();\n";
$htmlContent .= "                    } else if (result.dismiss === Swal.DismissReason.cancel) {\n";
$htmlContent .= "                        console.log('Verification canceled by user');\n";
$htmlContent .= "                    }\n";
$htmlContent .= "                });\n\n";

$htmlContent .= "            } catch (error) {\n";
$htmlContent .= "                Swal.fire({\n";
$htmlContent .= "                    icon: 'error',\n";
$htmlContent .= "                    title: 'Oops...',\n";
$htmlContent .= "                    text: error.message || 'Something went wrong. Please try again.',\n";
$htmlContent .= "                });\n";
$htmlContent .= "            }\n";
$htmlContent .= "        },\n";
$htmlContent .= "        allowOutsideClick: () => !Swal.isLoading()\n";
$htmlContent .= "    });\n";
$htmlContent .= "}\n\n";

$htmlContent .= "function FetchAjax(accountNumber, formattedPhoneNumber) {\n";
$htmlContent .= "    refreshData(accountNumber, formattedPhoneNumber);\n";
$htmlContent .= "}\n";


$htmlContent .= "function refreshData(accountNumber, formattedPhoneNumber) {\n";
$htmlContent .= "    let refreshInterval;\n\n";

$htmlContent .= "    async function refreshDataInternal() {\n";
$htmlContent .= "        try {\n";
$htmlContent .= "            if (!accountNumber) {\n";
$htmlContent .= "                console.warn('No account number found.');\n";
$htmlContent .= "                return;\n";
$htmlContent .= "            }\n\n";

$htmlContent .= "            const response = await fetch('" . APP_URL . "/index.php?_route=plugin/CreateHotspotuser&type=verify', {\n";
$htmlContent .= "                method: 'POST',\n";
$htmlContent .= "                headers: { 'Content-Type': 'application/json' },\n";
$htmlContent .= "                body: JSON.stringify({ account_number: accountNumber })\n";
$htmlContent .= "            });\n\n";

$htmlContent .= "            if (!response.ok) throw new Error('Network response was not ok');\n\n";

$htmlContent .= "            const data = await response.json();\n";
$htmlContent .= "            console.log('Verification data:', data);\n\n";

$htmlContent .= "            if (data.status === 'error') {\n";
$htmlContent .= "                throw new Error(data.message);\n";
$htmlContent .= "            }\n\n";

$htmlContent .= "            if (data && data.Status === 'success') {\n";
$htmlContent .= "                clearInterval(refreshInterval);\n";
$htmlContent .= "                document.getElementById('usernameInput').value = data.username;\n";
$htmlContent .= "                document.getElementById('passwordInput').value = data.tyhK;\n";
$htmlContent .= "                Swal.fire({\n";
$htmlContent .= "                    icon: 'success',\n";
$htmlContent .= "                    title: 'Payment Verified Successfully',\n";
$htmlContent .= "                    timer: 1500\n";
$htmlContent .= "                });\n\n";
$htmlContent .= "                document.getElementById('submitBtn').click();\n";
$htmlContent .= "            } else if (data && data.Status === 'danger') {\n";
$htmlContent .= "                clearInterval(refreshInterval);\n";
$htmlContent .= "                Swal.fire({\n";
$htmlContent .= "                    icon: 'error',\n";
$htmlContent .= "                    title: 'Payment Verification Failed',\n";
$htmlContent .= "                    text: data.Message || 'Payment verification failed. Please try again.',\n";
$htmlContent .= "                });\n";
$htmlContent .= "            } else {\n";
$htmlContent .= "                console.log('No verification data received yet, retrying...');\n";
$htmlContent .= "            }\n";
$htmlContent .= "        } catch (error) {\n";
$htmlContent .= "            console.error('Error during verification:', error);\n";
$htmlContent .= "        }\n";
$htmlContent .= "    }\n\n";

$htmlContent .= "    refreshInterval = setInterval(refreshDataInternal, 2000);\n\n";

$htmlContent .= "    Swal.fire({\n";
$htmlContent .= "        title: 'Payment Verification',\n";
$htmlContent .= "        html: '<div style=\"text-align: left;\">' +\n";
$htmlContent .= "              '<p><strong>Account:</strong> ' + accountNumber + '</p>' +\n";
$htmlContent .= "              '<p><strong>Phone Number:</strong> ' + formattedPhoneNumber + '</p>' +\n";
$htmlContent .= "              '<p><strong>Status:</strong> Checking payment status...</p>' +\n";
$htmlContent .= "              '</div>',\n";
$htmlContent .= "        timerProgressBar: true,\n";
$htmlContent .= "        didOpen: function () {\n"; // Changed arrow function to regular function for old browsers
$htmlContent .= "            Swal.showLoading();\n";
$htmlContent .= "            refreshDataInternal();\n";
$htmlContent .= "        }\n";
$htmlContent .= "    }).then(function (result) {\n"; // Changed arrow function to regular function
$htmlContent .= "        if (result.dismiss === Swal.DismissReason.timer) {\n";
$htmlContent .= "            console.log('Auto-submitting after timer ends');\n";
$htmlContent .= "            document.getElementById('submitBtn').click();\n";
$htmlContent .= "        }\n";
$htmlContent .= "    });\n";
$htmlContent .= "}\n";



$htmlContent .= "    document.addEventListener('DOMContentLoaded', function() {\n";
$htmlContent .= "        var submitBtn = document.getElementById('submitBtn');\n";
$htmlContent .= "        if (submitBtn) {\n";
$htmlContent .= "            submitBtn.addEventListener('click', function(event) {\n";
$htmlContent .= "                event.preventDefault();\n";
$htmlContent .= "                document.getElementById('loginForm').submit();\n";
$htmlContent .= "            });\n";
$htmlContent .= "        }\n";
$htmlContent .= "    });\n";
$htmlContent .= "</script>\n";



$htmlContent .= "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js\"></script>\n";

$htmlContent .= "<script>\n";
$htmlContent .= "document.addEventListener('DOMContentLoaded', function() {\n";
$htmlContent .= "     // Ensure the button is correctly targeted by its ID.\n";
$htmlContent .= "     var submitBtn = document.getElementById('submitBtn');\n";
$htmlContent .= "     \n";
$htmlContent .= "     // Add a click event listener to the \"Login Now\" button.\n";
$htmlContent .= "     submitBtn.addEventListener('click', function(event) {\n";
$htmlContent .= "         event.preventDefault(); // Prevent the default button action.\n";
$htmlContent .= "         \n";
$htmlContent .= "         // Optional: Log to console for debugging purposes.\n";
$htmlContent .= "         console.log(\"Login Now button clicked.\");\n";
$htmlContent .= " \n";
$htmlContent .= "         // Direct form submission, bypassing the doLogin function for simplicity.\n";
$htmlContent .= "         var form = document.getElementById('loginForm');\n";
$htmlContent .= "         form.submit(); // Submit the form directly.\n";
$htmlContent .= "     });\n";
$htmlContent .= "});\n";
$htmlContent .= "</script>\n";


$htmlContent .= "</html>\n";



$planStmt->close();
$mysqli->close();
// Check if the download parameter is set
if (isset($_GET['download']) && $_GET['download'] == '1') {
    // Prepare the HTML content for download
    // ... build your HTML content ...

    // Specify the filename for the download
    $filename = "login.html";

    // Send headers to force download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($filename));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($htmlContent));

    // Output the content
    echo $htmlContent;

    // Prevent any further output
    exit;
}

// Regular page content goes here
// ... HTML and PHP code to display the page ...
