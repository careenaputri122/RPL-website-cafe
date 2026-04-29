# Implementation Plan: Reservation Payment Flows

## Status: ✅ In Progress

## Steps:

### 1. Database Migration ✅ Complete
- ✅ Edited database_cafe.sql with new schema + migration SQL comments.
- ℹ️ **Run these SQLs on phpMyAdmin/XAMPP DB:**
  ```
  ALTER TABLE payment ADD COLUMN id_reservasi INT DEFAULT NULL AFTER id_pesanan;
  ALTER TABLE payment ADD INDEX idx_payment_reservasi (id_reservasi);
  ALTER TABLE payment DROP FOREIGN KEY fk_payment_pesanan, CHANGE id_pesanan id_pesanan INT DEFAULT NULL;
  ALTER TABLE payment ADD CONSTRAINT fk_payment_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON UPDATE CASCADE ON DELETE SET NULL;
  ALTER TABLE payment ADD CONSTRAINT fk_payment_reservasi FOREIGN KEY (id_reservasi) REFERENCES reservasi(id_reservasi) ON UPDATE CASCADE ON DELETE SET NULL;
  ```

### 2. Backend Functions (app/Support/data.php) ✅ Complete
- ✅ Added `find_payment_by_reservasi($resId)`
- ✅ Updated `get_payments()` SQL: LEFT JOIN pesanan/reservasi, show type
- ✅ Updated `map_payment_row()`: reservation_id, type fields

### 2. Backend Functions (app/Support/data.php) ✅ Complete
- ✅ Added `find_payment_by_reservasi($resId)`
- ✅ Updated `get_payments()` SQL: LEFT JOIN pesanan/reservasi, show type
- ✅ Updated `map_payment_row()`: reservation_id, type fields
- ✅ Modified `create_reservation()`: auto-creates payment record, returns `payment_id`

**Next Step 3.1: Update bootstrap.php routing for reservation payments**

### 3. Routing (app/bootstrap.php) [PENDING]
- [ ] Add POST payment_reservasi/upload
- [ ] Add POST admin/payment_reservasi/verify  
- [ ] Modify reservasi/store: redirect to payment?res_id=ID
- [ ] Update payment route: handle res_id param
- [ ] Admin/reservasi: link to payments

### 4. Views [PENDING]
- [ ] NEW: app/Views/customer/payment-reservasi.php (Rp15k simple)
- [ ] Update app/Views/customer/payment.php (support res_id)
- [ ] Update app/Views/admin/payment.php (show res-only payments)
- [ ] Update app/Views/admin/reservasi.php (payment status link)

### 5. Testing [PENDING]
- [ ] Jalur 1: Reservasi → payment auto → upload → admin verify → confirmed
- [ ] Jalur 2: Unchanged
- [ ] DB integrity, UI consistency

**Next: Start with DB migration.**

