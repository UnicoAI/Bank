<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Local Banks</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDCD0fBn2VAyGja7eXaABlde_U3_xEv_Fs&libraries=places"></script>
    <script src="js/scripts.js"></script>
    <link href="./../css/styles.css" rel="stylesheet">
    
</head>
<body>
<?php include './../navbar/navbar.php'; ?>

<div class="container">
 
    <!-- Information about Food Bank and Donations -->
    <div class="glass" role="alert">
        <h4 class="alert-heading">Helping the Homeless</h4>
        <p>Our mission is to provide nutritious meals to homeless individuals and families in need. We are committed to ensuring that no one in our community goes hungry. Through donations from kind-hearted people like you, we can continue to support the most vulnerable in our society.</p>
        <p>By visiting a local food bank, you not only provide immediate relief to those in need but also contribute to a sense of hope and humanity. Every little bit counts, whether it's a warm meal or a simple act of kindness.</p>
    </div>

    
    <h2 class="my-4">Find Local Food Banks</h2>
   
    <div class="row">
        
        <!-- Left Side: Map and Filters -->
        <div class="col-md-8">
            <div class="row">
                <!-- Filters on the Left -->
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <label for="radius">Search Radius (miles):</label>
                        <select id="radius" class="form-control" onchange="updateRadius()">
                            <option value="5">5 miles</option>
                            <option value="10" selected>10 miles</option>
                            <option value="15">15 miles</option>
                            <option value="20">20 miles</option>
                            <option value="1000">1000 miles</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="travel-mode">Travel Mode:</label>
                        <select id="travel-mode" class="form-control" onchange="fetchFoodBanks(currentLocation.lat, currentLocation.lng, getRadius())">
                            <option value="DRIVING">Driving</option>
                            <option value="WALKING">Walking</option>
                            <option value="BICYCLING">Bicycling</option>
                            <option value="TRANSIT">Transit</option>
                        </select>
                    </div>
                </div>

                <!-- Map Container -->
                <div class="col-md-12" style="height: 500px;">
                    <div id="map"></div>
                </div>
            </div>
        </div>

        <!-- Right Side: Food Banks List -->
        <div class="col-md-4">
            <h3>Nearby Food Banks</h3>
            <div id="food-banks" class="list-group">
                <!-- Filtered food banks will be displayed here -->
            </div>
        </div>
    </div>
    
</div>
<!-- Donation Call to Action Section -->

<div class="glass" role="alert">
            <h5 class="card-title">Make a Difference: Donate Today</h5>
            <p class="card-text">Your donation helps provide meals, shelter, and hope for those in need. Food banks rely on your generosity to continue their essential work. Whether you can donate food, money, or time, your contributions are always welcome. Together, we can make a lasting impact on our community.</p>
            <a href="https://buy.stripe.com/7sI292fWV9vSdZ69AA?locale=en-GB&__embed_source=buy_btn_1QMWGpDCQrwsyQEze28onzE3" class="btn-primary">Donate Now</a>
        </div>
    
<?php include './../footer/footer.php'; ?>
</body>

</html>
