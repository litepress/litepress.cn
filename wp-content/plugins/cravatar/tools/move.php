<?php
$end = 5000;

for ($start = 4652;$start <= $end; $start++ ) {
	copy("/www/server/data/qq@002dmail@002dhash/email_hash_{$start}.ibd", "/data/low-mysql/qq-mail-hash/email_hash_{$start}.ibd");

	unlink("/www/server/data/qq@002dmail@002dhash/email_hash_{$start}.ibd");
	copy("/data/low-mysql/qq-mail-hash-bak/email_hash_{$start}.ibd", "/www/server/data/qq@002dmail@002dhash/email_hash_{$start}.ibd");
}
