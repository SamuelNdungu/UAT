use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            // Add the user_id column as a foreign key
            // The column type should match your users table ID (usually bigIncrements/bigInteger)
            // It should be nullable if a claim can exist without a user_id, but the error suggests it's required.
            $table->foreignId('user_id')->nullable()->after('policy_id')->constrained('users');
            // Change ->nullable() to ->required() if every claim MUST have a user.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);
            // Then drop the column
            $table->dropColumn('user_id');
        });
    }
};