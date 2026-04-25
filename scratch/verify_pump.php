<?php
// Mock objects/logic to verify the PUMP formula

function calculateNewPump($oldStock, $oldPump, $newQty, $newPriceLocal) {
    if ($oldStock <= 0) {
        return $newPriceLocal;
    }
    return (($oldStock * $oldPump) + ($newQty * $newPriceLocal)) / ($oldStock + $newQty);
}

// Test Case 1: First purchase
$s1 = 0; $p1 = 0; $q1 = 10; $price1 = 100;
$res1 = calculateNewPump($s1, $p1, $q1, $price1);
echo "Test 1 (Stock 0): Expected 100, got $res1\n";

// Test Case 2: Second purchase
$s2 = 10; $p2 = 100; $q2 = 5; $price2 = 130;
$res2 = calculateNewPump($s2, $p2, $q2, $price2);
echo "Test 2 (Stock 10): Expected 110, got $res2\n";

// Test Case 3: Currency conversion
$taux = 10.5;
$priceUSD = 100;
$priceLocal = $priceUSD * $taux;
$res3 = calculateNewPump($s2, $p2, $q2, $priceLocal);
echo "Test 3 (Currency): Stock 10, Pump 100, Added 5 at 100$ ($priceLocal DH). Result PUMP: $res3\n";
// Calc: ((10 * 100) + (5 * 1050)) / 15 = (1000 + 5250) / 15 = 6250 / 15 = 416.666
