<?php

const INSERT_NEW_USER = "INSERT INTO users
    (email, surname, name, patronymic, password) VALUES
    (:email, :surname, :name, :patronymic, :password)";

const INSERT_NEW_UR = "INSERT INTO users_roles
    (user_email, role_id) VALUES
    (:user, :role)";

const GET_ALL_USERS = "SELECT email, password, name, patronymic
    FROM users
    WHERE email = :email";

const GET_USER_ROLE = "SELECT role_id
    FROM users_roles
    WHERE user_email = :email";

const GET_ALL_PRODUCTS = "SELECT product_id, product_name, price, author, description, product_type, product_image, product_stock
    FROM products
    ORDER BY product_id";

const LOAD_NEW_PRODUCT = "INSERT INTO products
    (product_id, product_name, price, author, description, product_type, product_image, product_stock) VALUES
    ((SELECT COALESCE(MAX(product_id), 0) + 1 FROM products),
    :title, :price, :author, :description,
    (SELECT type_id FROM product_types
    WHERE type_name = :category), :image, :stock)";

const UPDATE_PRODUCT = "UPDATE products
    SET product_name = :title,
        price = :price,
        author = :author,
        description = :description,
        product_type = (SELECT type_id FROM product_types WHERE type_name = :category),
        product_image = :image,
        product_stock = :stock
    WHERE product_id = :id";

const DELETE_PRODUCT = "DELETE FROM products
    WHERE product_id = :id";

const INSERT_CART_PRODUCT = "INSERT INTO cart
    (user_email, product_id, quantity) VALUES
    (:email, :id, 1)";

const GET_USER_CART = "SELECT product_id, product_name, price, author, product_image, product_stock, quantity
    FROM cart JOIN products USING(product_id)
    WHERE user_email = :email
    ORDER BY product_id";

const GET_CART_ITEM = "SELECT quantity
    FROM cart
    WHERE user_email = :email AND product_id = :id";

const GET_PRODUCT_STOCK = "SELECT product_stock
    FROM products
    WHERE product_id = :id";

const DECREASE_PRODUCT_STOCK = "UPDATE products
    SET product_stock = product_stock - 1
    WHERE product_id = :id AND product_stock > 0";

const INCREASE_PRODUCT_STOCK = "UPDATE products
    SET product_stock = product_stock + :qty
    WHERE product_id = :id";

const DECREASE_PRODUCT_STOCK_BY_QTY = "UPDATE products
    SET product_stock = product_stock - :qty
    WHERE product_id = :id AND product_stock >= :qty";

const UPDATE_PRODUCT_QUANTITY = "UPDATE cart
    SET quantity = quantity + 1
    WHERE user_email = :email AND product_id = :id";

const DECREASE_PRODUCT_QUANTITY = "UPDATE cart
    SET quantity = quantity - 1
    WHERE user_email = :email AND product_id = :id AND quantity > 1";

const DELETE_CART_PRODUCT = "DELETE FROM cart
    WHERE user_email = :email AND product_id = :id";

const CLEAR_USER_CART = "DELETE FROM cart
    WHERE user_email = :email";
?>
