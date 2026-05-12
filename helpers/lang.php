<?php

/**
 * Detect the requested language from headers.
 * Supports:
 *   - Custom header:  lang: ar
 *   - Standard HTTP:  Accept-Language: ar
 * Falls back to 'en' if not Arabic.
 */
function getRequestedLang(): string
{
    $headers = getallheaders();

    // 1. Check custom 'lang' header (case-insensitive key lookup)
    $lang = null;
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'lang') {
            $lang = trim($value);
            break;
        }
    }

    // 2. Fall back to Accept-Language
    if (!$lang) {
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'accept-language') {
                $lang = trim($value);
                break;
            }
        }
    }

    // Normalise: treat anything starting with 'ar' as Arabic
    if ($lang && strtolower(substr($lang, 0, 2)) === 'ar') {
        return 'ar';
    }

    return 'en';
}

/**
 * Return a translated message string.
 *
 * @param string      $key  Message key
 * @param string|null $lang Override language (uses header detection if null)
 */
function t(string $key, string $lang = null): string
{
    if ($lang === null) {
        $lang = getRequestedLang();
    }

    static $messages = [
        /* ==================== ENGLISH ==================== */
        'en' => [

            // --- General ---
            'database_error'         => 'Database connection failed',
            'json_error'             => 'JSON Encoding Error',
            'method_not_allowed'     => 'Method not allowed',
            'invalid_input'          => 'Invalid input',
            'all_fields_required'    => 'All fields are required',
            'invalid_image_type'     => 'Invalid image type',
            'image_upload_failed'    => 'Failed to convert/upload image',
            'image_required'         => 'Profile image is required',
            'image_too_large'        => 'Image size too large (max 2MB)',
            'invalid_image_file'     => 'Uploaded file is not a valid image',
            'logout_failed'          => 'Failed to logout',
            'only_items_available'   => 'Only {count} items available',

            // --- Auth (shared) ---
            'token_required'         => 'Token required',
            'invalid_token'          => 'Invalid, expired token or account not verified',
            'admin_token_required'   => 'Admin Token required',
            'invalid_admin_token'    => 'Invalid or expired admin token',
            'email_password_required'=> 'Email and password are required',
            'invalid_email'          => 'Invalid email format',
            'invalid_credentials'    => 'Invalid email or password',
            'token_valid'            => 'Token is valid',
            'logout_success'         => 'Logout successful',

            // --- Auth (user) ---
            'register_success'             => 'Registration successful. Verification code sent to email.',
            'email_exists_not_verified'    => 'Email already exists but not verified. Data updated and verification code resent.',
            'email_exists'                 => 'Email already exists',
            'verify_success'               => 'Email verified successfully.',
            'invalid_verify_code'          => 'Invalid verification code or email.',
            'code_sent'                    => 'Password reset code sent to your email',
            'email_not_found'              => 'User with this email does not exist',
            'email_not_verified'           => 'Email not verified. Please verify your email first.',
            'password_reset_success'       => 'Password updated successfully',
            'invalid_or_expired_code'      => 'Invalid or expired verification code',
            'reset_request_failed'         => 'Failed to process request',
            'password_update_failed'       => 'Failed to update password',
            'verification_email_failed'    => 'Failed to send verification email',
            'reset_email_failed'           => 'Failed to send password reset email',
            'login_success'                => 'Login successful',
            'admin_login_success'          => 'Admin login successful',
            'email_required'               => 'Email is required',

            // --- Categories ---
            'categories_retrieved'   => 'Categories retrieved successfully',
            'category_added'         => 'Category added successfully',
            'category_updated'       => 'Category updated successfully',
            'category_deleted'       => 'Category deleted successfully',
            'category_not_found'     => 'Category not found',
            'category_exists'        => 'Category already exists',
            'category_name_required' => 'Category name is required',
            'category_id_required'   => 'Category id is required',
            'categories_failed'      => 'Failed to retrieve categories',
            'category_add_failed'    => 'Failed to add category',
            'category_update_failed' => 'Failed to update category',
            'category_delete_failed' => 'Failed to delete category',

            // --- Products ---
            'products_retrieved'     => 'Products retrieved successfully',
            'product_added'          => 'Product added successfully',
            'product_updated'        => 'Product updated successfully',
            'product_deleted'        => 'Product deleted successfully',
            'product_not_found'      => 'Product not found',
            'product_id_required'    => 'Product id is required',
            'products_failed'        => 'Failed to retrieve products',
            'product_add_failed'     => 'Failed to add product',
            'product_update_failed'  => 'Failed to update product',
            'product_delete_failed'  => 'Failed to delete product',
            'invalid_price_range'    => 'Invalid price range',
            'invalid_id'             => 'Invalid ID provided',
            'product_retrieved'      => 'Product retrieved successfully',
            'error_fetching_product' => 'Error occurred while fetching product details',

            // --- Cart ---
            'cart_retrieved'         => 'Cart items retrieved successfully',
            'cart_item_added'        => 'Item added to cart',
            'cart_updated'           => 'Cart updated successfully',
            'cart_item_not_found'    => 'Cart item not found',
            'cart_item_removed_oos'  => 'Item removed (out of stock)',
            'cart_failed'            => 'Failed to retrieve cart items',
            'cart_add_failed'        => 'Failed to add item to cart',
            'cart_update_failed'     => 'Failed to update cart',
            'cart_remove_failed'     => 'Failed to remove item from cart',
            'cart_item_removed'      => 'Item removed from cart successfully',
            'cart_not_found'         => 'Item not found in cart',
            'cart_count_retrieved'   => 'Cart items count retrieved successfully',
            'cart_count_failed'      => 'Failed to retrieve cart items count',
            'quantity_invalid'       => 'Quantity must be a positive number',

            // --- Search ---
            'search_required'        => 'Search query is required',
            'search_min_length'      => 'Search query must be at least 2 characters',
            'suggestions_retrieved'  => 'Suggestions retrieved successfully',
            'no_suggestions'         => 'No suggestions found',
            'suggestions_failed'     => 'Failed to fetch suggestions',

            // --- Orders ---
            'order_created'          => 'Order created successfully',
            'order_not_found'        => 'Order not found',
            'orders_retrieved'       => 'Orders retrieved successfully',
            'orders_failed'          => 'Failed to retrieve orders',
            'order_cancelled'        => 'Order cancelled successfully',
            'order_cancel_failed'    => 'Failed to cancel order, or it belongs to a past status',
            'order_status_updated'   => 'Order status updated successfully',
            'invalid_order_status'   => 'Invalid order status',
            'cart_empty'             => 'Your cart is empty',
            'insufficient_stock'     => 'Insufficient stock for some products in your cart',
            'addresses_required'     => 'Shipping and billing addresses are required',

            // --- Order Status Labels ---
            'pending'                => 'Pending',
            'confirmed'              => 'Confirmed',
            'shipped'                => 'Shipped',
            'delivered'              => 'Delivered',
            'cancelled'              => 'Cancelled',

            // --- Notifications ---
            'notifications_fetched'  => 'Notifications fetched successfully',
            'notifications_marked_read' => 'Notification(s) marked as read',
            'fcm_token_required'     => 'FCM token is required',
            'fcm_token_saved'        => 'FCM token saved successfully',
            'user_id_required'       => 'user_id is required',
            'order_notification_sent' => 'Order notification sent',
            'broadcast_notification_sent' => 'Broadcast notification sent',
            'multiple_users_notification_sent' => 'Multiple users notification sent',
            'invalid_action'         => 'Invalid action. Use: order_update, new_product, or promo_multiple',

            'notif_order_placed_title' => 'Order Placed Successfully',
            'notif_order_placed_body'  => 'Your order #{order_id} has been placed and is pending confirmation.',
            'notif_order_cancelled_title' => 'Order Cancelled',
            'notif_order_cancelled_body'  => 'Your order #{order_id} has been successfully cancelled.',
            'notif_order_status_updated_title' => 'Order #{order_id} — {status_label}',
            'notif_order_status_updated_body'  => 'Your order status has been updated to: {status_label}.',
            'notif_new_product_title' => 'New Product Available 🔥',
            'notif_new_product_body'  => 'Tap to view the product.',
            'notif_promo_title' => 'Special Offer! ⚡',
            'notif_promo_body'  => 'Exclusive discount just for you!',
            'notif_security_alert_title' => 'Security Alert 🔐',
            'notif_security_alert_body'  => 'Your password was recently reset. If this was not you, contact support immediately.',
        ],

        /* ==================== ARABIC ==================== */
        'ar' => [

            // --- General ---
            'database_error'         => 'فشل الاتصال بقاعدة البيانات',
            'json_error'             => 'خطأ في ترميز JSON',
            'method_not_allowed'     => 'الطريقة غير مسموحة',
            'invalid_input'          => 'مدخلات غير صالحة',
            'all_fields_required'    => 'جميع الحقول مطلوبة',
            'invalid_image_type'     => 'نوع الصورة غير مدعوم',
            'image_upload_failed'    => 'فشل تحويل أو رفع الصورة',
            'image_required'         => 'صورة الملف الشخصي مطلوبة',
            'image_too_large'        => 'حجم الصورة كبير جدًا (الحد الأقصى 2 ميجابايت)',
            'invalid_image_file'     => 'الملف المرفوع ليس صورة صالحة',
            'logout_failed'          => 'فشل تسجيل الخروج',
            'only_items_available'   => 'متاح فقط {count} قطعة',

            // --- Auth (shared) ---
            'token_required'         => 'الرمز المميز مطلوب',
            'invalid_token'          => 'رمز غير صالح أو منتهي الصلاحية أو الحساب غير مُتحقق منه',
            'admin_token_required'   => 'رمز المشرف مطلوب',
            'invalid_admin_token'    => 'رمز المشرف غير صالح أو منتهي الصلاحية',
            'email_password_required'=> 'البريد الإلكتروني وكلمة المرور مطلوبان',
            'invalid_email'          => 'صيغة البريد الإلكتروني غير صحيحة',
            'invalid_credentials'    => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            'token_valid'            => 'الرمز المميز صالح',
            'logout_success'         => 'تم تسجيل الخروج بنجاح',

            // --- Auth (user) ---
            'register_success'             => 'تم التسجيل بنجاح. تم إرسال رمز التحقق إلى بريدك الإلكتروني.',
            'email_exists_not_verified'    => 'البريد الإلكتروني موجود لكن لم يتم التحقق منه. تم تحديث البيانات وإعادة إرسال رمز التحقق.',
            'email_exists'                 => 'البريد الإلكتروني موجود بالفعل',
            'verify_success'               => 'تم التحقق من البريد الإلكتروني بنجاح.',
            'invalid_verify_code'          => 'رمز التحقق أو البريد الإلكتروني غير صحيح.',
            'code_sent'                    => 'تم إرسال رمز إعادة تعيين كلمة المرور إلى بريدك الإلكتروني',
            'email_not_found'              => 'لا يوجد مستخدم بهذا البريد الإلكتروني',
            'email_not_verified'           => 'البريد الإلكتروني غير مُتحقق منه. يرجى التحقق من بريدك الإلكتروني أولاً.',
            'password_reset_success'       => 'تم تحديث كلمة المرور بنجاح',
            'invalid_or_expired_code'      => 'رمز التحقق غير صالح أو منتهي الصلاحية',
            'reset_request_failed'         => 'فشلت معالجة الطلب',
            'password_update_failed'       => 'فشل تحديث كلمة المرور',
            'verification_email_failed'    => 'فشل إرسال بريد التحقق الإلكتروني',
            'reset_email_failed'           => 'فشل إرسال بريد إعادة تعيين كلمة المرور',
            'login_success'                => 'تم تسجيل الدخول بنجاح',
            'admin_login_success'          => 'تم تسجيل دخول المشرف بنجاح',
            'email_required'               => 'البريد الإلكتروني مطلوب',

            // --- Categories ---
            'categories_retrieved'   => 'تم استرجاع الفئات بنجاح',
            'category_added'         => 'تمت إضافة الفئة بنجاح',
            'category_updated'       => 'تم تحديث الفئة بنجاح',
            'category_deleted'       => 'تم حذف الفئة بنجاح',
            'category_not_found'     => 'الفئة غير موجودة',
            'category_exists'        => 'الفئة موجودة بالفعل',
            'category_name_required' => 'اسم الفئة مطلوب',
            'category_id_required'   => 'معرّف الفئة مطلوب',
            'categories_failed'      => 'فشل استرجاع الفئات',
            'category_add_failed'    => 'فشلت إضافة الفئة',
            'category_update_failed' => 'فشل تحديث الفئة',
            'category_delete_failed' => 'فشل حذف الفئة',

            // --- Products ---
            'products_retrieved'     => 'تم استرجاع المنتجات بنجاح',
            'product_added'          => 'تمت إضافة المنتج بنجاح',
            'product_updated'        => 'تم تحديث المنتج بنجاح',
            'product_deleted'        => 'تم حذف المنتج بنجاح',
            'product_not_found'      => 'المنتج غير موجود',
            'product_id_required'    => 'معرّف المنتج مطلوب',
            'products_failed'        => 'فشل استرجاع المنتجات',
            'product_add_failed'     => 'فشلت إضافة المنتج',
            'product_update_failed'  => 'فشل تحديث المنتج',
            'product_delete_failed'  => 'فشل حذف المنتج',
            'invalid_price_range'    => 'نطاق السعر غير صالح',

            // --- Cart ---
            'cart_retrieved'         => 'تم استرجاع عناصر السلة بنجاح',
            'cart_item_added'        => 'تمت إضافة العنصر إلى السلة',
            'cart_updated'           => 'تم تحديث السلة بنجاح',
            'cart_item_not_found'    => 'عنصر السلة غير موجود',
            'cart_item_removed_oos'  => 'تمت إزالة العنصر (غير متوفر في المخزون)',
            'cart_failed'            => 'فشل استرجاع عناصر السلة',
            'cart_add_failed'        => 'فشلت إضافة العنصر إلى السلة',
            'cart_update_failed'     => 'فشل تحديث السلة',
            'cart_remove_failed'     => 'فشل إزالة العنصر من السلة',
            'cart_item_removed'      => 'تمت إزالة العنصر من السلة بنجاح',
            'cart_not_found'         => 'العنصر غير موجود في السلة',
            'cart_count_retrieved'   => 'تم استرجاع عدد عناصر السلة بنجاح',
            'cart_count_failed'      => 'فشل استرجاع عدد عناصر السلة',
            'quantity_invalid'       => 'يجب أن تكون الكمية رقمًا موجبًا',

            // --- Search ---
            'search_required'        => 'استعلام البحث مطلوب',
            'search_min_length'      => 'يجب أن يتكون استعلام البحث من حرفين على الأقل',
            'suggestions_retrieved'  => 'تم استرجاع الاقتراحات بنجاح',
            'no_suggestions'         => 'لا توجد اقتراحات',
            'suggestions_failed'     => 'فشل جلب الاقتراحات',

            // --- Orders ---
            'order_created'          => 'تم إنشاء الطلب بنجاح',
            'order_not_found'        => 'الطلب غير موجود',
            'orders_retrieved'       => 'تم استرجاع الطلبات بنجاح',
            'orders_failed'          => 'فشل استرجاع الطلبات',
            'order_cancelled'        => 'تم إلغاء الطلب بنجاح',
            'order_cancel_failed'    => 'فشل إلغاء الطلب، أو أن حالته لا تسمح بالإلغاء',
            'order_status_updated'   => 'تم تحديث حالة الطلب بنجاح',
            'invalid_order_status'   => 'حالة الطلب غير صالحة',
            'cart_empty'             => 'عربة التسوق فارغة',
            'insufficient_stock'     => 'كمية المخزون غير كافية لبعض المنتجات في السلة',
            'addresses_required'     => 'عناوين الشحن والفواتير مطلوبة',
            'invalid_id'             => 'المعرف المقدم غير صالح',
            'product_retrieved'      => 'تم استرجاع المنتج بنجاح',
            'error_fetching_product' => 'حدث خطأ أثناء جلب تفاصيل المنتج',

            // --- Order Status Labels ---
            'pending'                => 'قيد الانتظار',
            'confirmed'              => 'تم التأكيد',
            'shipped'                => 'تم الشحن',
            'delivered'              => 'تم التسليم',
            'cancelled'              => 'ملغي',

            // --- Notifications ---
            'notifications_fetched'  => 'تم جلب الإشعارات بنجاح',
            'notifications_marked_read' => 'تم تعليم الإشعار(ات) كمقروءة',
            'fcm_token_required'     => 'رمز FCM مطلوب',
            'fcm_token_saved'        => 'تم حفظ رمز FCM بنجاح',
            'user_id_required'       => 'معرّف المستخدم مطلوب',
            'order_notification_sent' => 'تم إرسال إشعار الطلب',
            'broadcast_notification_sent' => 'تم إرسال إشعار جماعي',
            'multiple_users_notification_sent' => 'تم إرسال إشعار لعدة مستخدمين',
            'invalid_action'         => 'إجراء غير صالح. استخدم: order_update أو new_product أو promo_multiple',

            'notif_order_placed_title' => 'تم إنشاء الطلب بنجاح',
            'notif_order_placed_body'  => 'تم تقديم طلبك رقم {order_id} وهو قيد انتظار التأكيد.',
            'notif_order_cancelled_title' => 'تم إلغاء الطلب',
            'notif_order_cancelled_body'  => 'تم إلغاء طلبك رقم {order_id} بنجاح.',
            'notif_order_status_updated_title' => 'طلب رقم {order_id} — {status_label}',
            'notif_order_status_updated_body'  => 'تم تحديث حالة طلبك إلى: {status_label}.',
            'notif_new_product_title' => 'منتج جديد 🔥',
            'notif_new_product_body'  => 'اضغط لمشاهدة المنتج.',
            'notif_promo_title' => 'عرض خاص! ⚡',
            'notif_promo_body'  => 'خصم حصري لك!',
            'notif_security_alert_title' => 'تنبيه أمني 🔐',
            'notif_security_alert_body'  => 'تمت إعادة تعيين كلمة المرور مؤخرًا. إذا لم تكن أنت، تواصل مع الدعم فورًا.',
        ],
    ];

    $message = $messages[$lang][$key] ?? $messages['en'][$key] ?? $key;

    $args = func_get_args();
    $replacements = $args[2] ?? [];
    if (is_array($replacements) && $replacements) {
        foreach ($replacements as $placeholder => $value) {
            $message = str_replace('{' . $placeholder . '}', (string)$value, $message);
        }
    }

    return $message;
}
