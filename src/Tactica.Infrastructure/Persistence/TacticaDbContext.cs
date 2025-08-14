using Microsoft.EntityFrameworkCore;
using Tactica.Infrastructure.Persistence;

namespace Tactica.Infrastructure.Persistence;

/// <summary>
/// EF Core database context for the Tactica application.
/// Holds DbSets and applies entity configurations.
/// </summary>
public class TacticaDbContext : DbContext
{
    /// <summary>
    /// Initializes a new instance of the <see cref="TacticaDbContext" />.
    /// </summary>
    /// <param name="options">The context options provided by dependency injection.</param>
    public TacticaDbContext(DbContextOptions<TacticaDbContext> options) : base(options) { }

    /// <inheritdoc />
    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

        modelBuilder.ApplyConfigurationsFromAssembly(typeof(TacticaDbContext).Assembly);
    }
}