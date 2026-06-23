Disclaimer & Intellectual Property
This repository contains a specific module for Stripe payment processing and order management extracted from my private application.

Open Source: The code regarding the payment system within this repository is released under the MIT License and is free to use for your own projects.

Private Property: Please note that this repository only contains the payment-related module. All other logic, design assets, and proprietary features of my original application remain my intellectual property and may not be used.

Project Structure & Usage

To help you understand how the payment and order management module works, I have organized the code into the following core areas:

Controllers: The logic for processing payments and handling orders is primarily located in:

PaymentController.php: Manages the Stripe payment lifecycle (escrow flow, webhooks, and dispute handling).

RequestController.php: Handles the initial creation of orders and file uploads.

PrinterController.php: Manages production statuses and part-specific cancellations.

Models: The data structure is defined in:

Request.php: Stores order details, file paths, and payment statuses.

Views: The interface for the payment flow can be found in:

request/create.blade.php: The initial upload and order form.

catalog/create.blade.php: The checkout summary for the catalog miniatures that have been put in the cart.

printer/dashboard.blade.php: The interface for managing production and disputes.

dashboard.blade.php: the user panel where partial and full refunds can be submitted. And where the claim for partial refund can be filled in and posted to print expert panel

PS: The documentation is written inline in the files so you can read what everything does and makes it easier to understand.
