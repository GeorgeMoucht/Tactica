using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Tactica.Infrastructure.Persistence;

namespace Tactica.Api.Controllers;

/// <summary>
/// Health endpoints for checking API liveness and dependencies (like the database).
/// </summary>
[ApiController]
[Route("api/[controller]")]
public sealed class HealthController : ControllerBase
{
    private readonly TacticaDbContext _db;

    /// <summary>
    /// Initializes a new instance of the <see cref="HealthController"/>.
    /// </summary>
    /// <param name="db">The database context used to verify connectivity.</param>
    public HealthController(TacticaDbContext db) => _db = db;

    /// <summary>
    /// Returns combined health status for API and database.
    /// </summary>
    [HttpGet]
    public async Task<IActionResult> Get(CancellationToken ct)
    {
        bool dbOk;
        try
        {
            dbOk = await _db.Database.CanConnectAsync(ct);
        }
        catch
        {
            dbOk = false;
        }

        var response = new
        {
            status = "ok",
            serverTime = DateTimeOffset.UtcNow,
            dependencies = new
            {
                database = dbOk ? "ok" : "unreachable"
            }
        };

        return dbOk ? Ok(response) 
                    : StatusCode(StatusCodes.Status503ServiceUnavailable, response);
    }
}
