<?php

require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseUser;


session_start();

$currUser = ParseUser::getCurrentUser();
if ($currUser){

    // Store current user session token, to restore in case we create new user
    $_SESSION['token'] = $currUser -> getSessionToken();
} else {

    header("Refresh:0; url=../index.php");
}

?>

<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <!-- <div class="col-md-5 align-self-center">
            <h3 class="text-white">Payments</h3> </div> -->
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Accounting</a></li>
                <li class="breadcrumb-item active">Payments</li>
            </ol>
        </div>
    </div>

    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start Page Content -->
        <div class="row bg-white m-l-0 m-r-0 box-shadow ">

        </div>
        <div class="row">
            <div class="col-lg">
                <div class="card">

                    <?php

                    $query = new ParseQuery('Payments');
                    $matchCounter = $query->count(true);

                    echo ' <h2 class="card-title">'.$matchCounter.' Payments in total</h2> ';

                    ?>

                    <div class="card-body">
                        <div class="table-responsive">
                            <!--<table class="table">-->
                            <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead class="bg-light">
                                <tr>
                                    <th style="color:#65131f ;">Date</th>
                                    <th style="color:#65131f ;">Trans ID</th>
                                    <th style="color:#65131f ;">SKU</th>
                                    <!--<th>Item</th>-->
                                    <th style="color:#65131f ;">Price</th>
                                    <th style="color:#65131f ;">Currency</th>
                                    <th style="color:#65131f ;">PayerID</th>
                                    <th style="color:#65131f ;">Payer</th>
                                    <th style="color:#65131f ;">Method</th>
                                    <th style="color:#65131f ;">Type</th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                try {

                                    $currUser = ParseUser::getCurrentUser();
                                    $cuObjectID = $currUser->getObjectId();

                                    $query = new ParseQuery("Payments");
                                    $query->descending('createdAt');
                                    $query->includeKey("author");
                                    $query->limit(1000000);
                                    $catArray = $query->find(true);

                                    foreach ($catArray as $iValue) {
                                        // Get Parse Object
                                        $cObj = $iValue;

                                        //$objectId = $cObj->getObjectId();
                                        $date= $cObj->getCreatedAt();
                                        $created = date_format($date,"d/m/Y");

                                        $transactionId = $cObj->get("transactionId");
                                        //$objectId = $cObj->getObjectId();
                                        $sku = $cObj->get("sku");

                                        //$itemName = $cObj->get('name');
                                        $itemPrice = $cObj->get('price');
                                        $currency = $cObj->get('currency');

                                        $payerId = $cObj->get('authorId');
                                        $payerName = $cObj->get('author')->get('name');

                                        $paymentMethod = $cObj->get('method');
                                        $paymentType = $cObj->get('type');

                                        if ($cObj->get('live_stream') !== null){
                                            $objectIdStream = $cObj->get('live_stream')->getObjectId();
                                        } else {
                                            $objectIdStream = '';
                                        }


                                        echo '
		            	
		            	        <tr>
                                    <td>'.$created.'</td>
                                    <td>'.$transactionId.'</td>
                                    <td>'.$sku.'</td>
                                    
                                    <td>'.$itemPrice.'</td>
                                    <td>'.$currency.'</td>
                                    <td>'.$payerId.'</td>
                                    <td>'.$payerName.'</td>
                                    <td>'.$paymentMethod.'</td>
                                    <td>'.$paymentType.'</td>
                                </tr>
                                
                                ';
                                    }
                                    // error in query
                                } catch (ParseException $e){ echo $e->getMessage(); }
                                ?>

                                </tbody>
                            </table>
                        </div>
                    </div>



                </div>
            </div>
        </div>

        <!-- End PAge Content -->
    </div>
    <!-- End Container fluid  -->
    <!-- footer -->

    <!-- End footer -->
</div>
