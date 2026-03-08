<?php

namespace Solvers\Dsql;

class ODKAPIClient
{
	private string $serverURL = "https://sdcedit.com/v1";
	private string $token = "";
	public string $errorMessage = "";
	public int $httpcode = 0;
	public $result = null;

	// Admin (service) account used only to provision new users & assignments.
	private string $adminEmail = "sse1.sakib@gmail.com";
	private string $adminPassword = "DkPas@12345678";

	public function __construct($email = null, $password = null)
	{
		// Only use static token if no user credentials are given
		$envToken = (!$email && !$password)
			? (getenv('ODK_BEARER_TOKEN') ?: (function_exists('env') ? env('ODK_BEARER_TOKEN') : null))
			: null;

		if ($envToken) {
			$this->token = $envToken;
			$this->log("Using static ODK_BEARER_TOKEN from environment.");
			return;
		}

		$this->log("No static token found, logging in with admin account.");
		try {
			$this->adminLogin();
		} catch (\Throwable $e) {
			$this->log("Admin login failed in constructor: " . $e->getMessage());
			$this->token = "";
		}

		// 🔧 Compatibility mode with old client
		if ($email && $password) {
			// Pad password like old logic
			$password = str_pad($password, 10, "0", STR_PAD_RIGHT);
			try {
				$this->loginOrCreateUser($email, $password);
				$this->log("Auto-login or create user success for $email");
			} catch (\Throwable $e) {
				$this->log("Auto-login/create user failed for $email: " . $e->getMessage());
			}
		}
	}

	private function log(string $msg): void
	{
		@file_put_contents(__DIR__ . "/debug.log", "[" . date('c') . "] " . $msg . "\n", FILE_APPEND);
	}

	/** Log in as the *admin* provisioning account. Throws on failure. */
	public function adminLogin(): void
	{
		$this->log("Admin login start");
		$this->getTokenFromServer($this->adminEmail, $this->adminPassword);
		if (empty($this->token)) {
			$this->log("Admin login FAILED: {$this->errorMessage} (HTTP {$this->httpcode})");
			throw new \Exception("Admin login failed: " . $this->errorMessage);
		}
		$this->log("Admin login OK");
	}

	/** Log in as user, or create + assign, then log in as that user. */
	public function loginOrCreateUser(string $email, string $password): void
	{
		$this->log("User login start for $email");
		$this->getTokenFromServer($email, $password);
		if (!empty($this->token)) {
			$this->log("User login OK for $email");
			return;
		}

		// If login failed, check if user already exists
		$this->adminLogin();
		$this->getJson("users?email=" . urlencode($email));

		if ($this->httpcode == 200 && !empty($this->result)) {
			// Find exact match
			$exactUser = null;
			foreach ($this->result as $u) {
				if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
					$exactUser = $u;
					break;
				}
			}

			if ($exactUser) {
				$userId = $exactUser['id'];
				if (!$this->patchJson("users/{$userId}", ["password" => $password])) {
					$this->log("❌ Failed to reset password for userId=$userId ({$email}) HTTP {$this->httpcode} msg={$this->errorMessage}");
				} else {
					$this->log("✅ Password reset OK for userId=$userId ({$email})");
				}
			} else {
				$this->log("⚠️ No exact email match found in users list, proceeding to create new user: {$email}");
				$this->createNewUser($email, $password);
				return;
			}
		} else {
			// No user found — create new one
			$this->createNewUser($email, $password);
			return;
		}

		// Need to create the user.
		$this->log("User login failed for $email: {$this->errorMessage} (HTTP {$this->httpcode})");
		$this->adminLogin();

		$displayName = explode("@", $email)[0];
		// Create user
		$resp = $this->postJson("users", [
			"email"       => $email,
			"password"    => $password,
			"displayName" => $displayName
		]);
		if ($resp === false) {
			$this->log("User creation FAILED for $email: {$this->errorMessage} (HTTP {$this->httpcode})");
			throw new \Exception("User creation failed: " . $this->errorMessage);
		}
		$createdUserId = $this->result['id'] ?? null;
		$this->log("User created id={$createdUserId}");

		// Assign role 5 (Data Collector) on project
		$projectId = getenv('ODK_PROJECT_ID') ?: (function_exists('env') ? env('ODK_PROJECT_ID') : null);
		if (!$projectId) {
			throw new \Exception("ODK_PROJECT_ID not configured.");
		}
		$resp = $this->postJson("projects/$projectId/assignments/5/" . $createdUserId);
		if ($resp === false) {
			$this->log("Project assignment FAILED for $email: {$this->errorMessage} (HTTP {$this->httpcode})");
			throw new \Exception("Project assignment failed: " . $this->errorMessage);
		}
		$this->log("Project assignment OK for user id={$createdUserId} on project {$projectId}");

		// Finally log in as that user
		$this->getTokenFromServer($email, $password);
		if (empty($this->token)) {
			$this->log("User login (after create) FAILED: {$this->errorMessage} (HTTP {$this->httpcode})");
			throw new \Exception("User login after create failed: " . $this->errorMessage);
		}
		$this->log("User login (after create) OK");
	}

	/** Public helper to manually fetch a new token */
	public function fetchNewToken(string $email, string $password): string
	{
		$this->getTokenFromServer($email, $password);
		return $this->token;
	}

	/** Low-level login/token retrieval */
	private function getTokenFromServer(string $email, string $password): void
	{
		$url = $this->serverURL . "/sessions";
		$payload = json_encode(["email" => $email, "password" => $password]);

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2
		]);

		$response = curl_exec($ch);
		$curlErr  = curl_error($ch);
		$this->httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// **Add this line here**
		$this->log("Second login attempt response: " . substr((string)$response, 0, 1000));

		$this->log("POST $url => HTTP {$this->httpcode}" . ($curlErr ? " CURLERR: $curlErr" : ""));
		$this->log("Response: " . substr((string)$response, 0, 500));

		$json = json_decode((string)$response, true);
		if ($this->httpcode !== 200 || empty($json['token'])) {
			$this->errorMessage = $json['message'] ?? ($curlErr ?: "Login failed or no token returned.");
			$this->token = "";
		} else {
			$this->token = $json['token'];
			$this->errorMessage = "";
		}
	}

	/** Fetch form; return version string or null. */
	public function getFormVersion(string $projectId, string $xmlFormId): ?string
	{
		$resp = $this->getJson("projects/$projectId/forms/$xmlFormId");
		if ($resp === false) return null;
		return $this->result['version'] ?? null;
	}

	/** Submit XML using raw POST (not multipart) to avoid IIS/CURL issues */
	public function submitXML(string $projectId, string $xmlFormId, string $xmlContent): bool
	{
		// $url = $this->serverURL . "/projects/$projectId/forms/$xmlFormId/submissions";
		$encodedId = rawurlencode($xmlFormId);
		$url = $this->serverURL . "/projects/$projectId/forms/$encodedId/submissions";


		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $xmlContent,
			CURLOPT_HTTPHEADER     => [
				"Authorization: Bearer " . $this->token,
				"Content-Type: text/xml",
				"Expect:" // disables the Expect: 100-continue header
			],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2,
		]);

		$this->log("Submitting XML to $url (encoded formId='$encodedId'), XML length=" . strlen($xmlContent));

		$response = curl_exec($ch);
		$curlErr  = curl_error($ch);
		$this->httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->log("POST $url => HTTP {$this->httpcode}" . ($curlErr ? " CURLERR: $curlErr" : ""));
		$this->log("Response: " . substr((string)$response, 0, 500));

		if ($this->httpcode >= 200 && $this->httpcode < 300) {
			$this->errorMessage = "";
			$this->result = json_decode((string)$response, true);
			return true;
		}
		if ($this->httpcode === 409) {
			$this->errorMessage = "Submission already exists.";
			$this->result = json_decode((string)$response, true);
			return false;
		}

		$this->errorMessage = $curlErr ?: (string)$response;
		$this->result = json_decode((string)$response, true);
		return false;
	}

	/** Get a one-time draft link for editing without login */
	public function getEnketoDraftUrl(string $projectId, string $xmlFormId, string $instanceId): ?string
	{
		// Use admin token
		// $url = $this->serverURL . "/projects/$projectId/forms/$xmlFormId/submissions/" . rawurlencode($instanceId) . "/draft";
		$encodedId = rawurlencode($xmlFormId);
		$url = $this->serverURL . "/projects/$projectId/forms/$encodedId/submissions/" . rawurlencode($instanceId) . "/draft";


		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true, // POST to create draft
			CURLOPT_HTTPHEADER     => [
				"Authorization: Bearer " . $this->token,
				"Content-Type: application/json"
			],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2
		]);

		$response = curl_exec($ch);
		$curlErr = curl_error($ch);
		$this->httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->log("POST $url => HTTP {$this->httpcode}" . ($curlErr ? " CURLERR: $curlErr" : ""));
		$this->log("Response: " . substr((string)$response, 0, 500));

		if ($this->httpcode !== 200) {
			$this->errorMessage = $curlErr ?: "Draft creation failed: HTTP {$this->httpcode}";
			return null;
		}

		$json = json_decode($response, true);
		if (empty($json['enketo_url'])) {
			$this->errorMessage = "Draft response missing enketo_url";
			return null;
		}

		return $json['enketo_url'];
	}

	/** Get official Enketo Edit URL (302 Location) and return it. */
	public function getEnketoEditUrl(string $projectId, string $xmlFormId, string $instanceId): ?string
	{
		// $url = $this->serverURL . "/projects/$projectId/forms/$xmlFormId/submissions/" . rawurlencode($instanceId) . "/edit";
		$encodedId = rawurlencode($xmlFormId);
		$url = $this->serverURL . "/projects/$projectId/forms/$encodedId/submissions/" . rawurlencode($instanceId) . "/edit";


		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => true,
			CURLOPT_NOBODY         => false,  // some proxies require GET with body
			CURLOPT_FOLLOWLOCATION => false,  // we want the Location header ourselves
			CURLOPT_HTTPHEADER     => [
				"Authorization: Bearer " . $this->token,
				"Content-Type: application/json"
			],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2
		]);

		$this->log("Fetching Enketo edit URL for '$xmlFormId' (encoded as '$encodedId') using instanceId=$instanceId");

		$response = curl_exec($ch);
		$curlErr  = curl_error($ch);
		$this->httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->log("GET $url => HTTP {$this->httpcode}" . ($curlErr ? " CURLERR: $curlErr" : ""));
		$this->log("Headers+Body (first 500): " . substr((string)$response, 0, 500));

		if (!in_array($this->httpcode, [302, 303], true)) {
			$this->errorMessage = $curlErr ?: "Expected redirect (302/303) but got {$this->httpcode}";
			return null;
		}

		// Extract Location header
		if (preg_match('/\r?\nLocation:\s*(.+)\r?\n/i', (string)$response, $m)) {
			$location = trim($m[1]);
			$this->log("Edit redirect Location: $location");
			return $location;
		}

		$this->errorMessage = "No Location header found in response.";
		return null;
	}

	/** Generic JSON GET helper */
	public function getJson(string $endpoint)
	{
		$url = $this->serverURL . "/$endpoint";
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => [
				"Authorization: Bearer " . $this->token,
				"Content-Type: application/json"
			],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2
		]);
		$response = curl_exec($ch);
		$curlErr  = curl_error($ch);
		$this->httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->log("GET $url => HTTP {$this->httpcode}" . ($curlErr ? " CURLERR: $curlErr" : ""));
		$this->log("Response: " . substr((string)$response, 0, 500));

		$this->result = json_decode((string)$response, true);
		if ($this->httpcode !== 200) {
			$this->errorMessage = $this->result['message'] ?? ($curlErr ?: "GET failed.");
			return false;
		}
		$this->errorMessage = "";
		return true;
	}

	/** Generic JSON POST helper (uses current token) */
	private function postJson(string $endpoint, array $body = [])
	{
		$url = $this->serverURL . "/$endpoint";
		$payload = json_encode($body);

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_HTTPHEADER     => [
				"Content-Type: application/json",
				"Authorization: Bearer " . $this->token
			],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2
		]);
		$response = curl_exec($ch);
		$curlErr  = curl_error($ch);
		$this->httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->log("POST $url => HTTP {$this->httpcode}" . ($curlErr ? " CURLERR: $curlErr" : ""));
		$this->log("Payload: " . $payload);
		$this->log("Response: " . substr((string)$response, 0, 500));

		$this->result = json_decode((string)$response, true);
		if (!in_array($this->httpcode, [200, 201, 204], true)) {
			$this->errorMessage = $this->result['message'] ?? ($curlErr ?: "POST failed.");
			return false;
		}
		$this->errorMessage = "";
		return true;
	}

	private function patchJson(string $endpoint, array $body = [])
	{
		$url = $this->serverURL . "/$endpoint";
		$payload = json_encode($body);

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST  => "PATCH",
			CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_HTTPHEADER     => [
				"Content-Type: application/json",
				"Authorization: Bearer " . $this->token
			],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2
		]);
		$response = curl_exec($ch);
		$curlErr  = curl_error($ch);
		$this->httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->log("PATCH $url => HTTP {$this->httpcode}" . ($curlErr ? " CURLERR: $curlErr" : ""));
		$this->log("Payload: " . $payload);
		$this->log("Response: " . substr((string)$response, 0, 500));

		$this->result = json_decode((string)$response, true);
		if (!in_array($this->httpcode, [200, 201, 204], true)) {
			$this->errorMessage = $this->result['message'] ?? ($curlErr ?: "PATCH failed.");
			return false;
		}
		$this->errorMessage = "";
		return true;
	}


	public function getToken(): string
	{
		return $this->token;
	}

	// --- Back-compat for editTrigger.php ---
	public function getDataFromServer(string $endpoint, string $type = "Array")
	{
		$url = $this->serverURL . '/' . ltrim($endpoint, '/');
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				"Authorization: Bearer " . $this->token,
				"Content-Type: application/json"
			],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2
		]);
		$response = curl_exec($ch);
		$this->httpcode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$this->result = $response;
		return $this->httpcode === 200;
	}

	public function deleteDataFromServer(string $endpoint)
	{
		$url = $this->serverURL . '/' . ltrim($endpoint, '/');
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST  => "DELETE",
			CURLOPT_HTTPHEADER => [
				"Authorization: Bearer " . $this->token,
				"Content-Type: application/json"
			],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2
		]);
		$response = curl_exec($ch);
		$this->httpcode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $this->httpcode === 200 || $this->httpcode === 204;
	}

	private function createNewUser(string $email, string $password): void
	{
		$displayName = explode("@", $email)[0];
		$this->log("Creating new user $email");

		$resp = $this->postJson("users", [
			"email" => $email,
			"password" => $password,
			"displayName" => $displayName
		]);

		if ($resp === false) {
			$this->log("❌ User creation FAILED for $email: {$this->errorMessage} (HTTP {$this->httpcode})");
			throw new \Exception("User creation failed: " . $this->errorMessage);
		}

		$createdUserId = $this->result['id'] ?? null;
		$projectId = getenv('ODK_PROJECT_ID') ?: (function_exists('env') ? env('ODK_PROJECT_ID') : null);
		if (!$projectId) {
			throw new \Exception("ODK_PROJECT_ID not configured.");
		}

		$resp = $this->postJson("projects/$projectId/assignments/5/" . $createdUserId);
		if ($resp === false) {
			$this->log("Project assignment FAILED for $email: {$this->errorMessage} (HTTP {$this->httpcode})");
			throw new \Exception("Project assignment failed: " . $this->errorMessage);
		}

		$this->log("✅ Created and assigned user $email (id=$createdUserId) to project $projectId");
		$this->getTokenFromServer($email, $password);
	}

	private function getRoleIdBySystem(string $system): ?int {
    if (!$this->getJson("roles") || !is_array($this->result)) return null;
    foreach ($this->result as $role) {
        if (($role['system'] ?? '') === $system) return (int)$role['id'];
    }
    return null;
}

private function grantProjectRole(int $projectId, int $roleId, int $userId): bool {
    return $this->postJson("projects/$projectId/assignments/$roleId/$userId") !== false;
}

/** Ensure user has both API submit role AND web-login role on the project */
private function ensureWebLoginAccess(int $userId, int $projectId): void {
    // Resolve role ids from your server (no guessing/hardcoding)
    $collectorId = $this->getRoleIdBySystem('project.data_collector'); // often no web login
    $viewerId    = $this->getRoleIdBySystem('project.viewer');        // web login allowed
    $managerId   = $this->getRoleIdBySystem('project.manager');       // web login allowed

    if (!$collectorId) { $collectorId = 5; } // fallback if your server uses static ids
    $webRoleId = $viewerId ?? $managerId;
    if (!$webRoleId) {
        throw new \Exception("Could not resolve a web-capable role (project.viewer/manager).");
    }

    // Assign roles idempotently (Central ignores duplicates)
    $this->grantProjectRole($projectId, $collectorId, $userId); // API submit
    $this->grantProjectRole($projectId, $webRoleId,   $userId); // Web UI login
}

}
