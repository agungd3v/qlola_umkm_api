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
		Schema::create('other_products', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("transaction_id");
			$table->unsignedBigInteger("outlet_id");
			$table->string("product_name")->nullable();
			$table->double("product_price");
			$table->integer("quantity");
			$table->double("total");
			$table->string("status")->default("paid");
			$table->timestamps();

			$table->foreign("transaction_id")->references("id")->on("transactions")->onUpdate("cascade")->onDelete("cascade");
			$table->foreign("outlet_id")->references("id")->on("outlets")->onUpdate("cascade")->onDelete("cascade");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('other_products');
	}
};
