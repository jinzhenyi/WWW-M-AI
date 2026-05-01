<?php
// 是否开启 TOTP 登录（true = 必须登录，false = 无需登录）
define('ENABLE_LOGIN', true);

// TOTP 密钥（Base32 字符串，大小写不敏感）
// 示例密钥，请务必改成自己生成的！
define('TOTP_SECRET', 'JBSWY3DPEHPK3PXP');