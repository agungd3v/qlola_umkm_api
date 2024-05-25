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
		Schema::create('products', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("business_id");
			$table->string("product_image")->nullable();
			$table->string("product_name");
			$table->double("product_price");
			$table->boolean("product_favorite")->default(0);
			$table->timestamps();

			$table->foreign("business_id")->references("id")->on("businesses")->onUpdate("cascade")->onDelete("cascade");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('products');
	}
};
