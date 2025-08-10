using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Tactica.Infrastructure;
using Tactica.Infrastructure.Persistence;

namespace Tactica.Api.Controllers;

/// <summary>
/// Health endpoints for liveness and readiness checks.
/// </summary>
[ApiController]
[Route("api/[controller]")]
public class HealthController : ControllerBase
{
    private readonly TacticaDbContext _db;

    /// <summary>
    /// Initializes a new health controller.
    /// </summary>
    public HealthController(TacticaDbContext db) => _db = db;

    /// <summary>
    /// Verifies the API is up and the database is reachable.
    /// </summary>
    [HttpGet("db")]
    public async Task<IActionResult> DbAsync(CancellationToken ct)
    {
        var canConnect = await _db.Database.CanConnectAsync(ct);
        return canConnect
            ? Ok(new { status = "ok" })
            : StatusCode(StatusCodes.Status503ServiceUnavailable, new { status = "db-unreachable" });
    }
}
