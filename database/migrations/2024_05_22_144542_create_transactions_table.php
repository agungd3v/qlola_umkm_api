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
		Schema::create('transactions', function (Blueprint $table) {
			$table->id();
			$table->string("transaction_code")->unique();
			$table->unsignedBigInteger("business_id");
			$table->unsignedBigInteger("outlet_id");
			$table->integer("grand_total");
			$table->enum("status", ["success", "void", "pending"])->default("pending");
			$table->timestamps();

			$table->foreign("business_id")->references("id")->on("businesses")->onUpdate("cascade")->onDelete("cascade");
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
		Schema::dropIfExists('transactions');
	}
};
