package controllers

import java.time.Instant
import javax.inject._

import io.swagger.annotations._
import models.{Post, PostRepository, PostResponse, PostsResponse}
import play.api.libs.json.Json
import play.api.mvc._

import scala.concurrent.ExecutionContext

// UI
//TODO: Frontend
//TODO: Selenium Tests
//TODO: Production ready DockerCompose file
@Api(value = "/posts")
class PostController @Inject()(cc: ControllerComponents, repository: PostRepository, implicit val ec: ExecutionContext)
  extends InjectedController {

//  ,
//  @ApiParam(value = "ISO Date from when to filter") from: Option[Instant],
//  @ApiParam(value = "ISO Date until when to filter") to: Option[Instant]

  @ApiOperation(value = "Display all VDM posts", response = classOf[Post], responseContainer = "List")
  def posts(@ApiParam(value = "Name of the author to filter by") author: Option[String], from: Option[Instant]) = Action.async {
    repository.getAll(author, from).map(posts => {
      Ok(Json.toJson(PostsResponse(posts, posts.length)))
    })
  }

  @ApiOperation(value = "Display one VDM post", response = classOf[Post])
  @ApiResponses(Array(
    new ApiResponse(code = 404, message = "Post not found")
  ))
  def post(@ApiParam(value = "The id of the post to display", required = true) id: String) = Action.async { request =>
    repository.getOne(id).map {
      case Some(p) => Ok(Json.toJson(PostResponse(p)))
      case None => NotFound
    }
  }
}
