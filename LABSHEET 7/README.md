# Paper & Pine Bookshop

A small PHP and MySQL bookstore project for LABSHEET 7. It uses custom responsive CSS, PDO prepared statements, session authentication, CSRF tokens, and server-side validation.

## Run locally

1. Install PHP 8.1+ with the PDO MySQL extension and MySQL 8+.
2. Import `schema.sql` into MySQL. The script creates the `bookstore` database and adds sample books.
3. Set database connection environment variables if needed:
   - `BOOKSTORE_DSN` (default: `mysql:host=127.0.0.1;dbname=bookstore;charset=utf8mb4`)
   - `BOOKSTORE_DB_USER` (default: `root`)
   - `BOOKSTORE_DB_PASSWORD` (default: empty)
4. From the directory containing `LABSHEET 7`, start PHP's development server:

   ```sh
   php -S localhost:8000
   ```

5. Open `http://localhost:8000/LABSHEET%207/`.
6. Register a user, then promote that account to admin in MySQL:

   ```sql
   UPDATE bookstore.users SET role = 'admin' WHERE email = 'you@example.com';
   ```

   Sign out and sign in again to load the admin role into the session.

## Pages

- `index.php`: featured shelf, search by title/author/category, category filter, and pagination
- `book.php`: book details, stock, reviews and ratings, add to bag, wish list
- `register.php`, `login.php`, `logout.php`: account management
- `cart.php`, `checkout.php`, `invoice.php`: shopping bag, stock-locked checkout, and order confirmation
- `orders.php`, `wishlist.php`: customer order history and saved books
- `admin/index.php`: add books and update price/stock
- `admin/orders.php`: review orders and set Pending, Shipped, or Delivered status
- `admin/reports.php`: completed revenue, order totals, pending count, and top sellers

Orders and line items are created in one database transaction. Book stock is checked when adding to the session cart and locked and checked again during checkout. Shipping is $4.95 below $45 and free for orders of $45 or more. Review submissions require a signed-in account, and one review per user/book can be updated.

The pages use the `/LABSHEET%207/` URL prefix, so start the development server from the workspace directory one level above this project folder.
