using Microsoft.EntityFrameworkCore;
using Tactica.Infrastructure;

namespace Tactica.Infrastructure;

/// <summary>
/// EF Core database context for the Tactica Application.
/// </summary>
public class TacticaDbContext : DbContext
{
    /// <summary>
    /// Initializes a new instance of <see cref="TacticaDbContext" />.
    /// </summary>
    /// <param name="options">The context options supplied by DI.</param>
    public TacticaDbContext(DbContextOptions<TacticaDbContext> options) : base(options)
    {
    }

    // Add DbSet<TEntity> properties here when you create entities, e.g.:
    // public DbSet<Project> Projects => Set<Project>();

    /// <inheritdoc />
    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);
        
        // Keep model configuration in separate classes later via IEntityTypeConfiguration<T>.
        // Example: modelBuilder.ApplyConfigurationsFromAssembly(typeof(TacticaDbContext).Assembly);
    }
}