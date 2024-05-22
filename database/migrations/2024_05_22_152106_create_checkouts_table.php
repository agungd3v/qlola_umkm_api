<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('checkouts', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("transaction_id");
			$table->unsignedBigInteger("product_id");
			$table->integer("quantity");
			$table->double("total");
			$table->string("status")->default("paid");
			$table->timestamps();

			$table->foreign("product_id")->references("id")->on("products")->onUpdate("cascade")->onDelete("cascade");
			$table->foreign("transaction_id")->references("id")->on("products")->onUpdate("cascade")->onDelete("cascade");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('checkouts');
	}
};
