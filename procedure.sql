DELIMITER $$
CREATE  PROCEDURE `PACKAGE_ACTIVATION`(IN var_member_id int(11), IN var_package_id INT(11), IN var_transaction_id VARCHAR(255), IN var_wallet VARCHAR(60), IN var_created_by VARCHAR(60), IN var_created_at DATETIME, OUT status INT(11), OUT message VARCHAR(100))
    -- NO SQL
BEGIN
    DECLARE var_exist INT(1);
    DECLARE var_valid_relation INT(1);
    DECLARE var_created_by_id INT(8);
    DECLARE is_admin INT(1) DEFAULT 0;
    DECLARE is_balance_deducted INT(1) DEFAULT 0;
    DECLARE var_particulars VARCHAR(100);
    DECLARE var_current_balance DECIMAL(50,8);
    DECLARE var_new_balance DECIMAL(50,8);

    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET status = 0;
        SET message = 'An error has occurred, operation was terminated!';

        SELECT status, message;
    END;

    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE; -- SERIALIZABLE is the strictest SQL transaction isolation level
    START TRANSACTION;

        SELECT COUNT(1) INTO var_exist FROM member WHERE member_id = var_member_id;
        IF var_exist = 1 THEN

            IF var_created_by = 'admin' THEN
                SET var_exist = 1;
                SET var_created_by_id = 0;
                SET var_valid_relation = 1;
                SET var_particulars = 'admin_activation';
                SET is_admin = 1;
            ELSE
                SELECT COUNT(1), member_id INTO var_exist, var_created_by_id FROM member WHERE member_code = var_created_by;
                
                IF var_created_by_id = var_member_id THEN
                    SET var_valid_relation = 1;
                    SET var_particulars = 'self_activation';
                ELSE
                    SELECT COUNT(1) INTO var_valid_relation FROM member WHERE member_id = var_member_id AND intro_tree LIKE CONCAT('%',var_created_by,'%');
                    
                    SET var_particulars = 'downline_activation';
                END IF;
                
            END IF;

            IF var_exist = 1 AND var_valid_relation = 1 THEN
                    
                # get package from package_id
                SELECT amount INTO message FROM packages WHERE id = var_package_id;
                
                IF is_admin = 0 THEN
                    SELECT COUNT(1), balance INTO var_exist, var_current_balance FROM fund_wallet_balance  WHERE member_id=var_created_by_id;  

                    IF var_exist = 1 AND var_current_balance >= message THEN

                        SET var_new_balance = var_current_balance - message;
                        
                        UPDATE fund_wallet_balance 
                        SET transaction_type='debit', amount=message, balance=var_new_balance, created_at=var_created_at 
                        WHERE member_id=var_created_by_id;
                        
                        INSERT INTO fund_wallet_transaction
                        (member_id, transaction_id, transaction_type, debit, balance, particulars, created_at, created_by) VALUES
                        (var_created_by_id, var_transaction_id, 'debit', message, var_new_balance, var_particulars, var_created_at, var_created_by);

                        SET is_balance_deducted = 1;
                    ELSE
                        SET status = 0;
                        SET message = 'Do not have enough balance in your wallet.';
                    END IF;
                END IF;

                IF is_admin = 1 OR is_balance_deducted = 1 THEN
                    # Final task
                    INSERT INTO member_packages
                    (member_id, package_id, gross_amount, tax, net_amount, wallet, created_at, created_by)VALUES
                    (var_member_id, var_package_id, message, 0, message, var_wallet, var_created_at, var_created_by);
                
                    SET status = LAST_INSERT_ID();
                    SET message = 'Package successfully activated';
                    
                END IF;
            ELSE
                SET status = 0;
                SET message = 'Login Member is not a valid or the member is not in your downline.';
            END IF;
        ELSE
            SET status = 0;
            SET message = 'Invalid logged in member.';
        END IF;

    COMMIT;

    SELECT status, message;
END$$
DELIMITER ;