# PROJECT: Enterprise Gaming Digital Store & Recharge Platform

## ROLE & EXPECTATIONS

Act as a Principal Software Architect, Senior Laravel Engineer, UI/UX Designer, E-commerce Product Architect, DevOps Engineer, SEO Specialist, AI Automation Engineer, and MCP Developer.

Your mission is to design, architect, implement, test, and deliver a complete, production-ready Gaming Digital Store platform.

This is NOT a demo, prototype, or simple e-commerce website.

Build a professional, scalable, secure digital products platform specializing in:

- Gaming Gift Cards.
- Gaming Wallet Cards.
- Game Top-Ups.
- Direct Game Recharge.
- Gaming Subscriptions.
- PlayStation products.
- Xbox products.
- Steam Wallet.
- Nintendo eShop.
- Roblox.
- PUBG Mobile.
- Free Fire.
- Mobile Legends.
- Fortnite.
- Riot Games.
- Razer Gold.
- Google Play.
- Apple Gift Cards.
- Other digital entertainment products.

The platform must support adding new gaming platforms, products, suppliers, and payment gateways without changing the core architecture.

The initial business model should support international customers, with Egypt as a dedicated local payment market.

---

# PHASE 1: DISCOVERY & TECHNICAL ARCHITECTURE

Before writing code:

1. Inspect the existing project, repository, installed packages, infrastructure, and database.
2. Understand the existing coding conventions.
3. Identify missing dependencies and architectural problems.
4. Create a comprehensive technical architecture document.
5. Create an implementation roadmap with milestones.
6. Identify external integrations and their documentation.
7. Identify potential security, scalability, and operational risks.

Do not destroy or overwrite existing working functionality.

Create:

- docs/PROJECT_ARCHITECTURE.md
- docs/DATABASE_DESIGN.md
- docs/API_DOCUMENTATION.md
- docs/INTEGRATIONS.md
- docs/SECURITY.md
- docs/SEO_STRATEGY.md
- docs/N8N_AUTOMATIONS.md
- docs/MCP_SERVER.md
- docs/DEPLOYMENT.md
- docs/IMPLEMENTATION_ROADMAP.md

Use a modular Laravel architecture with clear separation of business domains.

Suggested domains:

- Catalog
- Gaming Platforms
- Digital Products
- Suppliers
- Supplier Integrations
- Pricing
- Currency
- Inventory
- Orders
- Payments
- Customers
- Wallet
- Promotions
- Coupons
- Affiliates
- Reviews
- Notifications
- Support
- Analytics
- SEO
- Automation
- MCP

Use Repository + Service patterns where appropriate.

Avoid unnecessary abstractions and overengineering.

---

# PHASE 2: PROFESSIONAL UI/UX DESIGN USING FIGMA MCP

IMPORTANT: Figma MCP is already connected.

Before implementing any frontend code, create the complete design system and all major screens in Figma.

Do not start coding the storefront before completing and reviewing the Figma designs.

## Design Direction

Create a premium international gaming marketplace.

Visual direction:

- Modern gaming aesthetic.
- Premium dark-first interface.
- Professional typography.
- Beautiful gradients and subtle lighting.
- High-quality gaming visuals.
- Modern product cards.
- Smooth interactions.
- Responsive layouts.
- Excellent mobile experience.
- Clear visual hierarchy.
- High conversion-focused UX.

Avoid generic AI-generated dashboard designs.

The design should feel like a real international gaming brand.

## Figma Requirements

Create:

1. Complete Design System.
2. Color Variables.
3. Typography System.
4. Spacing System.
5. Buttons and Form Components.
6. Product Card Components.
7. Navigation Components.
8. Modal Components.
9. Tables and Data Components.
10. Responsive Layout Components.
11. Storefront Pages.
12. Admin Dashboard Pages.
13. Authentication Pages.
14. Checkout Experience.
15. Customer Dashboard.

Create reusable components and proper component variants.

Design desktop, tablet, and mobile layouts.

Use Figma MCP to create and organize all design files and frames.

After completing the design, inspect the results and ensure visual consistency before implementation.

---

# PHASE 3: VISUAL ASSETS USING CANVA MCP

IMPORTANT: Canva MCP is already connected.

Use Canva MCP to create or source the visual assets required by the project.

All visual assets should match the Figma design system.

Create:

- Homepage hero banners.
- Gaming category banners.
- Platform banners.
- Promotional banners.
- Product promotional visuals.
- Marketing graphics.
- Campaign graphics.
- Empty states where appropriate.
- Social media promotional assets.

Use genuine product/platform branding where permitted.

Do not invent official logos or use misleading product artwork.

Use properly licensed assets and respect brand guidelines.

Export assets in optimized formats such as WebP or AVIF where supported.

Organize assets into a clear folder structure.

Never use random placeholder images in the final production UI.

If Canva MCP cannot provide a particular asset, document the missing asset instead of silently substituting a poor-quality image.

---

# PHASE 4: COMPLETE STOREFRONT DEVELOPMENT

## Technology Stack

Backend:
- Latest stable Laravel version compatible with the project environment.
- PHP.
- MySQL.
- Laravel Queues.
- Laravel Scheduler.
- Redis where appropriate.
- REST APIs.
- Laravel Sanctum where authentication is required.

Frontend:
- Tailwind CSS.
- Vanilla JavaScript or modular JavaScript.
- Vite.
- HTML.
- CSS when necessary.
- No Livewire.

All frontend JavaScript and CSS must be organized as Vite-managed files.

Do not put large inline scripts or styles inside Blade templates.

Use reusable Blade components and layouts.

## Storefront Pages

Build the complete customer-facing website.

### 1. Homepage

Include:

- Professional hero section.
- Featured gaming platforms.
- Trending games.
- Best-selling products.
- Popular gift cards.
- Special offers.
- Recommended products.
- Recently added products.
- Promotional banners.
- Customer reviews.
- Trust and security indicators.
- FAQ.
- Newsletter subscription.
- Professional footer.

All sections must be manageable from the admin panel.

### 2. Gaming Platforms Page

Display all available gaming platforms with:

- Platform logo.
- Banner.
- Description.
- Available products.
- Supported regions.
- Supported currencies.
- Popularity.
- SEO metadata.

### 3. Platform Details

Each platform should have its own SEO-friendly page.

Example:

/gaming/playstation
/gaming/xbox
/gaming/steam
/gaming/roblox

### 4. Category Pages

Examples:

/gift-cards
/game-top-ups
/subscriptions
/gaming-wallets

Support filtering, sorting, search, and pagination.

### 5. Product Details

Each product should support:

- Product title.
- Description.
- Product images.
- Platform.
- Category.
- Region.
- Denomination.
- Product price.
- Original price.
- Discount.
- Availability.
- Delivery type.
- Delivery time.
- Supported payment methods.
- Product instructions.
- Terms and conditions.
- Related products.
- Customer reviews.
- SEO metadata.

Support variable product configurations.

For example, Steam Wallet:

- 5 USD
- 10 USD
- 20 USD
- 50 USD
- 100 USD

And regional variants such as US, UK, EU, and other supported regions.

### 6. Search

Create professional global search with:

- Instant suggestions.
- Search by game name.
- Search by platform.
- Search by product.
- Search by SKU.
- Popular searches.
- Search history where appropriate.

### 7. Shopping Cart

Support:

- Multiple products.
- Quantity where applicable.
- Price recalculation.
- Coupon application.
- Currency display.
- Remove and update items.
- Checkout validation.

### 8. Checkout

Create a simple, conversion-optimized checkout.

Support:

- Guest checkout.
- Registered customers.
- Email verification.
- Order summary.
- Coupon validation.
- Country selection.
- Currency display.
- Payment method selection.
- Order confirmation.

### 9. Customer Account

Create:

- Dashboard.
- Profile management.
- Orders.
- Order details.
- Digital code history.
- Wallet.
- Transactions.
- Wishlist.
- Support tickets.
- Reviews.
- Notifications.
- Security settings.

Customers must be able to access previously purchased digital products securely.

---

# PHASE 5: MULTI-SUPPLIER INTEGRATION ENGINE

This is one of the most important parts of the project.

Build a professional supplier integration architecture similar to Reloadly.

The platform must NOT depend on a single supplier.

## Initial Supplier

Implement Reloadly integration using its official API documentation.

Research the actual API capabilities and supported endpoints before implementation.

Support the available features, including:

- Product catalog synchronization.
- Product details.
- Countries and regions.
- Available denominations.
- Pricing.
- Product availability.
- Order creation.
- Digital code delivery.
- Transaction status.
- Balance checking.
- Transaction history.

Only implement features actually supported by the provider's API.

## Supplier Adapter Architecture

Create a common supplier interface.

Every supplier should implement standardized operations where supported:

- getProducts()
- getProductDetails()
- getCountries()
- getPricing()
- checkAvailability()
- getBalance()
- createOrder()
- getOrderStatus()
- cancelOrder()
- refundOrder()

Unsupported capabilities must be explicitly declared.

Do not assume every supplier supports the same functionality.

Create independent adapters for each supplier.

Future integrations should be possible without changing the checkout or order business logic.

## Admin Supplier Management

Create a complete supplier management system:

- Add supplier.
- Edit supplier.
- Activate/deactivate supplier.
- Configure API credentials.
- Configure sandbox/production mode.
- Configure API endpoints.
- Test API connection.
- Synchronize catalog.
- Synchronize pricing.
- View API logs.
- View failed requests.
- Monitor supplier balance.
- Configure supplier priority.
- Configure fallback supplier.
- Configure timeout settings.
- Configure retry policies.

Store API credentials encrypted.

Never expose supplier credentials to frontend code.

## Multi-Supplier Product Mapping

One internal product may be supplied by multiple suppliers.

Create a mapping system:

Internal Product -> Supplier A Product
Internal Product -> Supplier B Product
Internal Product -> Supplier C Product

Allow:

- Primary supplier.
- Secondary supplier.
- Supplier priority.
- Supplier-specific pricing.
- Supplier-specific availability.
- Supplier-specific regional restrictions.

Implement intelligent supplier selection.

If the primary supplier fails before a transaction is accepted, the system may attempt a safe fallback supplier.

Never execute the same customer order twice.

Implement idempotency, transaction locking, duplicate detection, and supplier-specific reconciliation.

Do not automatically retry an order if the supplier may already have processed it.

Such orders must enter a pending verification state until their actual status is established.

---

# PHASE 6: DIGITAL PRODUCT DELIVERY ENGINE

Support multiple delivery types.

1. Instant digital code.
2. Direct game top-up.
3. Account-based recharge.
4. API-based delivery.
5. Manual fulfillment.
6. Subscription activation.

Each product must define its delivery method.

For direct top-ups, support configurable required fields, such as:

- Player ID.
- Server ID.
- Region.
- Username.
- Account identifier.

Required fields must be configurable from the admin panel.

## Order Lifecycle

Implement a reliable order state machine.

Suggested statuses:

- Pending Payment.
- Payment Processing.
- Paid.
- Processing.
- Supplier Pending.
- Completed.
- Failed.
- Cancelled.
- Refund Pending.
- Refunded.
- Manual Review.

Maintain a full order event history.

Digital codes must be encrypted at rest where appropriate and accessible only to authorized customers after successful fulfillment.

Never expose a digital code in public APIs, logs, analytics, or error messages.

Implement secure delivery and email notifications.

---

# PHASE 7: PAYMENT GATEWAYS

Create a modular payment gateway architecture.

Initial gateways:

## 1. Stripe

International payments.

Support the payment methods and currencies actually enabled for the merchant account.

Implement:

- Payment Intent.
- Webhooks.
- Payment status verification.
- Refunds where supported.
- Failed payments.
- Idempotency.
- Payment logs.

## 2. PayPal

International payments.

Implement:

- Order creation.
- Payment approval.
- Capture.
- Webhook verification.
- Refunds.
- Payment reconciliation.

## 3. Paymob

Dedicated Egypt payment gateway.

Paymob should be available only to eligible Egyptian customers and transactions configured for EGP.

Implement the supported Paymob payment methods based on the merchant account and official API documentation.

## Payment Rules

Build configurable payment eligibility rules.

Example:

Egyptian customer:
- Paymob.
- Other eligible gateways if configured.

International customer:
- Stripe.
- PayPal.

Do not assume that country alone guarantees payment eligibility. Validate gateway account capabilities, currency support, customer location, and provider restrictions.

## Important Currency Architecture

The platform should support all configured currencies.

Customers can browse and see converted prices based on their selected display currency.

Example:

Internal product base price: USD 10.

Display:
- USD.
- EUR.
- GBP.
- EGP.
- SAR.
- AED.

Implement:

- Exchange rate provider.
- Automatic currency updates.
- Manual exchange rate overrides.
- Exchange rate history.
- Conversion precision.
- Configurable markup.
- Exchange rate caching.
- Currency fallback.
- Currency selection.

CRITICAL:

Display currency and payment currency are two separate concepts.

At checkout, the final amount must be converted into the actual currency supported by the selected payment gateway.

Never charge a gateway in a currency it does not support.

Show customers:

- Display price.
- Actual payment amount.
- Actual payment currency.

Store the original base price, conversion rate, gateway currency, and final charged amount with every order.

Never recalculate historical order prices using current exchange rates.

---

# PHASE 8: PRICING & PROFIT MANAGEMENT

Build a professional pricing engine.

Support:

- Supplier cost.
- Base product cost.
- Fixed markup.
- Percentage markup.
- Product-specific markup.
- Platform-specific markup.
- Category-specific markup.
- Country-specific pricing.
- Currency-specific pricing.
- Minimum profit margin.
- Promotional pricing.
- Dynamic pricing rules.

Calculate actual profitability after:

- Supplier cost.
- Payment gateway fees.
- Currency conversion costs.
- Other configured transaction costs.

Show admin:

- Revenue.
- Gross profit.
- Net profit.
- Supplier expenses.
- Gateway expenses.
- Product profitability.
- Best-selling products.
- Loss-making products.
- Profit margin by supplier.
- Profit by payment method.
- Profit by country.
- Profit by currency.

Allow admins to define minimum acceptable profit margins.

Prevent accidental selling below configured cost thresholds unless explicitly authorized.

---

# PHASE 9: COMPLETE ADMIN PANEL

Build a powerful, modern, user-friendly administration dashboard.

## Admin Technology Requirements

- Laravel backend.
- Blade.
- Tailwind CSS.
- Vanilla JavaScript / modular JavaScript.
- Vite.
- No Livewire.
- No Inertia unless explicitly justified and approved.
- Repository Pattern.
- Service Layer.
- Form Request Validation.
- API-based admin interactions.
- Yajra DataTables.
- Select2 or a more modern searchable select alternative.
- SweetAlert2 or an equivalent professional notification library.
- Chart.js or another suitable chart library.

All admin interactions should communicate with Laravel endpoints through clean, structured API requests.

Validation errors must be returned in a consistent JSON format and displayed professionally beside the corresponding form fields.

Use server-side DataTables for large datasets.

Implement:

- Search.
- Filtering.
- Sorting.
- Pagination.
- Bulk actions.
- Export.
- Column visibility.
- Saved filters where useful.

## Admin Dashboard Modules

### 1. Dashboard

- Total revenue.
- Orders.
- Customers.
- Profit.
- Pending orders.
- Failed transactions.
- Supplier balances.
- Gateway performance.
- Top-selling products.
- Recent orders.
- Revenue charts.
- Profit charts.
- Date range filters.

### 2. Catalog Management

- Gaming platforms.
- Categories.
- Products.
- Product variants.
- Denominations.
- Regions.
- Product images.
- Product attributes.
- Required delivery fields.
- Product availability.
- Featured products.

### 3. Supplier Management

- Supplier accounts.
- API credentials.
- Product mapping.
- Supplier pricing.
- Balance monitoring.
- API logs.
- Synchronization history.
- Supplier health.
- Fallback configuration.

### 4. Orders Management

- All orders.
- Order details.
- Customer details.
- Supplier details.
- Payment details.
- Digital delivery.
- Order event timeline.
- Retry/reconciliation tools.
- Refund management.
- Manual fulfillment.
- Fraud review.

### 5. Customers

- Customer profiles.
- Order history.
- Wallet.
- Transactions.
- Account status.
- Risk indicators.
- Customer notes.
- Support history.

### 6. Payments

- Payment gateways.
- Gateway credentials.
- Gateway configuration.
- Transaction history.
- Webhook logs.
- Refunds.
- Failed payments.
- Reconciliation.

### 7. Pricing & Currency

- Pricing rules.
- Markup rules.
- Exchange rates.
- Currency management.
- Profit reports.

### 8. Marketing

- Coupons.
- Discount campaigns.
- Promotions.
- Featured products.
- Homepage banners.
- Cross-selling.
- Upselling.
- Affiliate management.

### 9. Content Management

- Pages.
- Blog.
- FAQs.
- Menus.
- Homepage sections.
- SEO settings.
- Redirects.
- Media library.

### 10. Support

- Tickets.
- Customer conversations.
- Order-related issues.
- Refund requests.
- Internal notes.
- Ticket statuses.

### 11. Users & Permissions

Use Spatie Laravel Permission.

Create granular permissions.

Support:

- Super Admin.
- Admin.
- Operations Manager.
- Finance Manager.
- Support Agent.
- Catalog Manager.
- Marketing Manager.
- SEO Manager.

Each employee should only see and access authorized modules and actions.

### 12. System Settings

- General settings.
- Branding.
- SMTP.
- Currency.
- Localization.
- Payment configuration.
- Supplier configuration.
- SEO.
- Analytics.
- Notifications.
- Integrations.
- Security.
- API settings.
- Automation settings.

---

# PHASE 10: CUSTOM MCP SERVER FOR THE PLATFORM

Build a dedicated Model Context Protocol server for this platform.

The objective is to allow authorized AI agents and tools such as Claude Desktop and Claude Code to interact with the store safely.

Create an independent MCP server project with clear documentation.

Suggested architecture:

- TypeScript.
- Official MCP SDK.
- Secure API communication with Laravel.
- Token-based authentication.
- Scoped permissions.
- Audit logging.
- Input validation.
- Rate limiting.
- Structured responses.
- Error handling.

Do not expose the Laravel database directly to MCP clients.

MCP should communicate with Laravel through dedicated protected APIs.

## MCP Tools

Implement useful tools such as:

### Products
- list_products
- get_product
- search_products
- get_product_pricing
- check_product_availability

### Orders
- list_orders
- get_order_details
- get_order_status
- search_customer_orders

### Analytics
- get_sales_summary
- get_profit_summary
- get_top_selling_products
- get_supplier_performance
- get_failed_orders
- get_conversion_metrics

### Inventory & Suppliers
- get_supplier_balances
- get_supplier_status
- get_supplier_sync_status
- get_low_margin_products

### Marketing
- get_best_selling_products
- get_underperforming_products
- get_coupon_performance
- get_customer_purchase_trends

### Operations
- get_pending_orders
- get_failed_transactions
- get_pending_reconciliation
- get_system_health

### Administrative Actions

Allow selected actions only with explicit permission and appropriate confirmation.

Examples:

- update_product_price
- activate_product
- deactivate_product
- create_coupon
- trigger_catalog_sync
- retry_verified_failed_order

Never allow AI agents to:

- Access raw payment credentials.
- Retrieve unrestricted customer personal data.
- Read encrypted digital codes without explicit authorization.
- Execute unrestricted database queries.
- Issue refunds without authorization.
- Change critical settings without approval.

Implement read-only mode by default.

Write operations must use scoped permissions, validation, audit logs, and confirmation mechanisms.

Create:

- docs/MCP_SERVER.md
- MCP setup instructions.
- Claude Desktop configuration example.
- Claude Code configuration example.
- Tool schemas.
- Authentication documentation.
- Security documentation.

---

# PHASE 11: N8N AUTOMATION DEPARTMENT

n8n is a core part of the platform.

Build an automation architecture that connects Laravel with n8n using secure webhooks and APIs.

The Laravel application must remain the source of truth.

n8n should orchestrate workflows, not replace core transactional business logic.

Prepare the platform to support self-hosted n8n on Ubuntu VPS behind Nginx.

Do not require Docker.

Create a complete collection of importable n8n workflows.

Organize workflows by category.

## A. DAILY OPERATIONS

1. Daily Sales Report.
2. Daily Profit Report.
3. Failed Orders Monitoring.
4. Pending Orders Monitoring.
5. Supplier Balance Monitoring.
6. Supplier API Health Monitoring.
7. Payment Gateway Failure Monitoring.
8. Low Margin Product Alerts.
9. Catalog Synchronization.
10. Exchange Rate Updates.
11. Daily Revenue Comparison.

## B. WEEKLY BUSINESS INTELLIGENCE

1. Weekly Business Report.
2. Best-Selling Games.
3. Best-Selling Platforms.
4. Most Profitable Products.
5. Underperforming Products.
6. Supplier Performance Comparison.
7. Payment Gateway Performance.
8. Customer Retention Analysis.
9. Coupon Performance.
10. Pricing Optimization Recommendations.

## C. SMART MARKETING

1. Discover trending gaming products.
2. Identify popular gaming platforms.
3. Suggest new products.
4. Generate promotional campaigns.
5. Recommend product bundles.
6. Suggest coupon campaigns.
7. Generate SEO blog ideas.
8. Generate SEO descriptions.
9. Generate product marketing content.
10. Prepare social media content.

AI-generated recommendations must be reviewed before publication.

## D. CUSTOMER AUTOMATION

1. Order confirmation.
2. Payment confirmation.
3. Digital product delivery notifications.
4. Failed order notifications.
5. Customer support notifications.
6. Abandoned cart reminders.
7. Review requests.
8. Customer reactivation campaigns.
9. Personalized recommendations.

Respect customer consent, privacy requirements, and communication preferences.

## E. FINANCIAL MONITORING

1. Daily revenue and expenses.
2. Payment reconciliation.
3. Supplier balance alerts.
4. Gateway fee monitoring.
5. Profit margin alerts.
6. Unusual transaction monitoring.
7. Refund monitoring.

## F. SYSTEM MONITORING

1. Website uptime.
2. Queue failure monitoring.
3. Laravel exception monitoring.
4. Scheduled task monitoring.
5. Database health.
6. API latency.
7. Supplier API availability.
8. Webhook delivery failures.

## n8n Requirements

Create:

- Webhook authentication.
- Dedicated API credentials.
- Workflow execution logs.
- Retry policies.
- Error workflows.
- Notifications.
- Duplicate execution protection.
- Rate limiting.
- Failure alerts.

Every workflow must have documentation describing:

- Purpose.
- Trigger.
- Required credentials.
- Nodes.
- Input.
- Output.
- Failure handling.
- Schedule.
- How to import and activate it.

Create:

/n8n/workflows/
/n8n/documentation/
/n8n/credentials.example.md

Never commit real credentials.

---

# PHASE 12: SEO & GOOGLE ANALYTICS

SEO is a critical requirement.

Implement comprehensive technical and on-page SEO.

Target excellent SEO quality, with a measurable Lighthouse SEO score of 100 where technically achievable.

Do not claim that any score guarantees Google rankings.

## Technical SEO

- Server-side rendered Blade pages.
- SEO-friendly URLs.
- Canonical URLs.
- XML sitemap.
- Dynamic sitemap indexes.
- Robots.txt.
- Breadcrumbs.
- Open Graph.
- Twitter Cards.
- Structured Data.
- Schema.org.
- Product schema.
- Offer schema.
- Organization schema.
- Website schema.
- Breadcrumb schema.
- Article schema.
- FAQ schema where eligible.
- Correct pagination handling.
- Hreflang for supported languages and regions.
- Image optimization.
- Lazy loading.
- Internal linking.
- Redirect management.
- 404 and 410 handling.
- Duplicate content prevention.
- Meta title and description.
- SEO-friendly product descriptions.

Create dynamic SEO templates for:

- Homepage.
- Platform pages.
- Category pages.
- Product pages.
- Blog pages.
- Static pages.

## Google Integrations

Integrate:

1. Google Analytics 4.
2. Google Tag Manager.
3. Google Search Console.

Provide settings for:

- GA4 Measurement ID.
- GTM Container ID.
- Search Console verification.
- Conversion tracking.

Track relevant e-commerce events:

- view_item_list
- select_item
- view_item
- add_to_cart
- remove_from_cart
- begin_checkout
- add_payment_info
- purchase
- refund

Ensure purchase events are deduplicated and use verified order/payment data.

Add a Google Search Console integration where supported by Google's official API.

Provide reporting for:

- Clicks.
- Impressions.
- CTR.
- Average position.
- Top search queries.
- Top landing pages.

Never expose private Google API credentials in frontend code.

---

# PHASE 13: SECURITY & RELIABILITY

This platform processes money and delivers digital goods, so security must be treated as a first-class requirement.

Implement:

- CSRF protection.
- XSS protection.
- SQL injection protection.
- Secure authentication.
- Role-based access.
- Rate limiting.
- Secure password policies.
- Email verification.
- Secure password reset.
- Encrypted sensitive settings.
- Webhook signature verification.
- Payment idempotency.
- Supplier idempotency.
- Audit logging.
- Suspicious transaction detection.
- Secure digital code delivery.
- Session management.
- API authorization.
- Queue monitoring.
- Database transactions.
- Proper exception handling.

Never trust frontend payment confirmation.

Always verify payment status server-side.

Never fulfill an order based only on a browser redirect.

Use verified payment webhooks and provider-side status checks.

Build safe order reconciliation procedures.

---

# PHASE 14: DATABASE & CODE QUALITY

Use professional Laravel conventions.

## Required Architecture

- Controllers.
- Form Requests.
- Services.
- Repositories.
- Models.
- DTOs where useful.
- Enums.
- Events.
- Listeners.
- Jobs.
- Notifications.
- Console Commands.
- Policies.
- API Resources.
- Middleware.

Keep controllers thin.

Keep business logic out of Blade templates and Eloquent models where it does not belong.

Use database indexes and foreign keys.

Avoid N+1 queries.

Use eager loading.

Use transactions for financial operations.

Use queues for expensive or external operations.

Use Laravel Scheduler for scheduled application tasks.

Create meaningful migrations and seeders.

Use proper naming conventions.

Avoid giant controllers and giant service classes.

## Important Database Entities

Design the schema around entities such as:

- Users
- Roles
- Permissions
- Gaming Platforms
- Categories
- Products
- Product Variants
- Product Regions
- Suppliers
- Supplier Products
- Supplier Product Mappings
- Supplier Transactions
- Supplier Sync Logs
- Currencies
- Exchange Rates
- Pricing Rules
- Payment Gateways
- Payments
- Payment Transactions
- Payment Webhooks
- Orders
- Order Items
- Order Events
- Digital Codes
- Customer Wallets
- Wallet Transactions
- Coupons
- Promotions
- Reviews
- Wishlists
- Support Tickets
- Notifications
- SEO Metadata
- Blog Posts
- Audit Logs
- Automation Logs

This is a suggested starting point, not a rigid schema. Refine it based on the actual business requirements.

---

# PHASE 15: TESTING

Do not consider a feature complete merely because the UI works.

Create automated tests.

Required coverage:

- Authentication.
- Authorization.
- Product catalog.
- Product pricing.
- Currency conversion.
- Supplier integrations.
- Supplier failure handling.
- Duplicate order prevention.
- Checkout.
- Stripe.
- PayPal.
- Paymob.
- Webhook verification.
- Payment reconciliation.
- Digital code delivery.
- Refunds.
- Admin permissions.
- MCP authentication and permissions.
- n8n webhook authentication.

Use mocked supplier and payment responses for automated tests.

Do not perform real financial transactions during testing.

Test sandbox integrations independently.

Include failure scenarios, timeout scenarios, duplicate webhook scenarios, and interrupted transaction scenarios.

---

# PHASE 16: PERFORMANCE & DEPLOYMENT

Optimize for production.

Requirements:

- Laravel production optimization.
- Vite production builds.
- Asset compression.
- Image optimization.
- Browser caching.
- Redis caching where useful.
- Database query optimization.
- Queue workers.
- Supervisor configuration.
- Laravel Scheduler.
- Nginx configuration.
- SSL.
- Environment variables.
- Logging.
- Backups.
- Monitoring.
- Error reporting.

Target:

- Excellent Core Web Vitals.
- Fast mobile experience.
- Lighthouse Performance score of 90+ where achievable.
- Lighthouse SEO score of 100 where technically achievable.
- Accessibility improvements.
- No major browser console errors.

Do not sacrifice security or accessibility to achieve Lighthouse scores.

---

# PHASE 17: EXECUTION RULES

You must follow this implementation sequence.

## Step 1: Discovery

Inspect the project and produce the technical architecture.

## Step 2: Figma Design

Use Figma MCP to design the complete storefront and admin panel.

Do not proceed with frontend coding before the design system and core pages are reviewed.

## Step 3: Canva Assets

Use Canva MCP to prepare the visual assets and organize them.

## Step 4: Database & Backend

Implement migrations, models, services, repositories, permissions, and API endpoints.

## Step 5: Supplier Engine

Implement Reloadly first, then prepare the adapter architecture for additional suppliers.

## Step 6: Payment Engine

Implement Stripe, PayPal, and Paymob independently.

## Step 7: Storefront

Implement all customer-facing pages using the approved Figma designs.

## Step 8: Admin Panel

Implement all management modules using the approved Figma designs.

## Step 9: MCP Server

Build and test the dedicated MCP server.

## Step 10: n8n

Create all required workflows and documentation.

## Step 11: SEO & Analytics

Implement technical SEO, Google Analytics, Tag Manager, and Search Console.

## Step 12: Testing

Run automated and manual tests.

## Step 13: Production Readiness

Perform a final security, performance, and integration audit.

---

# NON-NEGOTIABLE RULES

1. Do not build a simple demo.
2. Do not use Livewire.
3. Do not use fake supplier integrations.
4. Do not simulate successful payments in production.
5. Do not expose API credentials.
6. Do not hardcode exchange rates.
7. Do not hardcode supplier-specific logic into the order system.
8. Do not create duplicate customer orders.
9. Do not fulfill unpaid orders.
10. Do not use random placeholder images in production.
11. Do not ignore mobile responsiveness.
12. Do not skip validation.
13. Do not skip automated tests.
14. Do not put business logic in controllers.
15. Do not generate a giant monolithic codebase.
16. Do not claim an integration works without testing it.
17. Do not invent API capabilities that providers do not support.
18. Do not leave unfinished TODOs while claiming the project is complete.
19. Never silently change an existing working feature.
20. Document every important architectural decision.

If an API credential, account, or provider access is missing, build the complete adapter structure, configuration interface, and mock tests, and clearly mark the integration as awaiting credentials or sandbox verification.

Do not block unrelated development because an external provider is unavailable.

---

# FINAL DELIVERABLES

At the end, I expect:

1. Complete Figma design project.
2. Complete Canva visual assets.
3. Fully functional Laravel application.
4. Complete customer storefront.
5. Complete admin panel.
6. Multi-supplier integration engine.
7. Reloadly integration.
8. Stripe integration.
9. PayPal integration.
10. Paymob integration.
11. Currency conversion engine.
12. Pricing and profit management.
13. Digital product delivery engine.
14. Dedicated MCP server.
15. Complete importable n8n workflows.
16. Google Analytics integration.
17. Google Search Console integration.
18. Complete SEO implementation.
19. Automated tests.
20. Deployment documentation.
21. API documentation.
22. Security documentation.
23. Operations documentation.

# WORKING STYLE

Work like a senior engineering team delivering a real commercial platform.

Be proactive and make sensible technical decisions.

Do not ask me about every small implementation detail.

When an important business decision is required, explain the options and recommend the best one.

Maintain a clear progress tracker.

After every milestone:

- Summarize completed work.
- List created files.
- List migrations.
- List implemented features.
- List tests and results.
- List outstanding requirements.
- Identify the next milestone.

Prioritize correctness, security, maintainability, scalability, and customer experience.

The final result must be a professional, production-ready international gaming digital marketplace, not just a visually attractive website.